<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use App\Models\UserBalance;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = User::find(auth()->user()->id);
        $order = $request->get('order', 'created_at');
        $orderBy = $request->get('order_by', 'desc');
        $role = $request->get('role');
        $since = $request->get('since');
        $until = $request->get('until');
        $search = $request->get('search');
        $per_page = $request->get('per_page', 10);
        $paginated = $request->get('paginated', 'yes');
        $country = $request->get('country');
        $bank = $request->get('bank');
        $users = User::query()
            ->where('users.delete', false)
            ->leftjoin('banks_accounts', 'users.id', '=', 'banks_accounts.user_id')
            ->join('countries', 'users.country_id', '=', 'countries.id')
            ->select('users.*', 'countries.name as country_name')
            ->groupBy('users.id');
        if ($search) {
            $users = $users
                ->select('users.*', 'countries.name as country_name')
                ->where(function ($users) use ($search) {
                    $users->where('users.name', 'LIKE', "%{$search}%")
                        ->orWhere('users.email', 'LIKE', "%{$search}%")
                        ->orWhere('countries.name', 'LIKE', "%{$search}%");
                });
        }
        if ($since) {
            $users = $users->where('users.created_at', '>=', $since);
        }
        if ($until) {
            $users = $users->where('users.created_at', '<=', $until);
        }
        //Filtrate users if that users has a bank account in the bank
        if ($bank) {
            $users = $users->where('banks_accounts.bank_id', $bank);
        }
        if ($role) {
            $users = $users->where('role_id', '=', $role);
        }
        if ($country) {
            $users = $users->where('country_id', '=', $country);
        }
        if ($order) {
            $users = $users->orderBy("users.$order", "$orderBy");
        }

        $users = $users->where('users.id', '!=', auth()->user()->id)->with('role', 'country', 'store'); // Exclude current user
        if ($currentUser->role->id === 1) {
            $users = $users->with('balance.currency', 'workingDays', 'lastReport'); // Include just admin
        }
        if ($paginated === 'no') {
            return response()->json($users->get(), 200);
        }

        return response()->json($users->paginate($per_page), 200);
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        $currentUser = User::find(auth()->user()->id);
        if ($currentUser->role->id === 1) {
            $messages = [
                'name.required' => 'El nombre es requerido',
                'email.unique' => 'Usuario ya registrado',
                'email.required' => 'Email requerido',
                'password.required' => 'Contraseña requerida',
                'password.confirmed' => 'Contraseña no coincide con la confirmación',
                'password.min' => 'Contraseña requiere minimo 8 caracteres',
                'password.max' => 'Contraseña máximo 16 caracteres',
                'country.exist' => 'País no registrado',
                'role.exist' => 'Rol inválido',
                'allowed_currencies.required' => 'Monedas permitidas requeridas',
                'allowed_currencies.*.exist' => 'Moneda no registrada',
                'allowed_countries.*.exist' => 'País no registrado',
            ];
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|max:16|confirmed',
                'image' => 'image',
                'country' => 'required|exists:countries,id',
                'role' => 'required|exists:roles,id',
                'allowed_currencies' => 'required|array',
                'allowed_currencies.*' => 'exists:currencies,id',
                'allowed_countries' => 'array',
                'allowed_countries.*' => 'exists:countries,id',
            ], $messages);
            if ($validatedData['role'] == 5 || $validatedData['role'] == 6) {
                $request->validate([
                    'currency' => 'required|exists:currencies,id',
                ],
                [
                    'currency.exist' => 'Moneda no registrada',
                ]);
            }
            if (isset($validatedData['image'])) {
                $imageName = time().'.'.$request->image->extension();
                $request->image->move(public_path('images'), $imageName);
                $validatedData['img'] = asset('images/'.$imageName);
            }

            $user = null;
            try {
                DB::transaction(function () use ($request, &$user) {
                    $user = User::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'country_id' => $request->country,
                        'role_id' => $request->role,
                        'permissions' => json_encode([
                            'allowed_currencies' => $request->allowed_currencies,
                            'allowed_countries' => $request->allowed_countries,
                        ]),
                    ]);
                    if ($user->role_id == 5 || $user->role_id == 6) {
                        UserBalance::create([
                            'user_id' => $user->id,
                            'currency_id' => $request->currency,
                        ]);
                    }
                });

                return response()->json($user, 201);
            } catch (Error $e) {
                return response()->json(['message' => $e], 500);
            }
        }

        return response()->json(['message' => 'forbiden'], 401);
    }

    public function show($id)
    {
        $currentUser = User::find(auth()->user()->id);
        if ($currentUser->role->id === 1) {
            $user = User::with('role', 'country')->find($id);
            if ($user) {
                return response()->json($user, 200);
            }

            return response()->json(['message' => 'not found'], 404);
        }

        return response()->json(['message' => 'forbiden'], 401);
    }

    public function update(Request $request, $id)
    {
        $currentUser = User::find(auth()->user()->id);
        if ($currentUser->role->id === 1) {
            $messages = [
                'name.required' => 'El nombre es requerido',
                'email.unique' => 'Usuario ya registrado',
                'email.required' => 'Email requerido',
                'password.required' => 'Contraseña requerida',
                'password.confirmed' => 'Contraseña no coincide con la confirmación',
                'password.min' => 'Contraseña requiere minimo 8 caracteres',
                'password.max' => 'Contraseña máximo 16 caracteres',
                'country_id.exist' => 'País no registrado',
                'role_id.exist' => 'Rol inválido',
            ];
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'password' => 'min:8|max:16|confirmed',
                'allowed_currencies' => 'array',
                'allowed_currencies.*' => 'exists:currencies,id',
                'image' => 'image',
            ], $messages);

            if (isset($validatedData['image'])) {
                $imageName = time().'.'.$request->image->extension();
                $request->image->move(public_path('images'), $imageName);
                $validatedData['img'] = asset('images/'.$imageName);
            }

            $user = User::find($id);

            foreach ($validatedData as $field => $value) {
                if ($field === 'email') {
                    $user->$field = $user->email;
                }
                if ($field === 'country_id') {
                    $user->$field = $user->country_id;
                }
                if ($field === 'role_id') {
                    $user->$field = $user->role_id;
                }
                if ($field === 'allowed_currencies') {
                    $user->permissions = json_encode([
                        'allowed_currencies' => $value,
                    ]);
                }
                else{
                    $user->$field = $value;
                }
            }
            $user->save();

            return response()->json(['message' => 'exito'], 201);
        } elseif ($currentUser->id == $id) {
            $messages = [
                'name.required' => 'El nombre es requerido',
                'email.unique' => 'Usuario ya registrado',
                'email.required' => 'Email requerido',
                'password.required' => 'Contraseña requerida',
                'password.confirmed' => 'Contraseña no coincide con la confirmación',
                'password.min' => 'Contraseña requiere minimo 8 caracteres',
                'password.max' => 'Contraseña máximo 16 caracteres',
                'country_id.exist' => 'País no registrado',
                'role_id.exist' => 'Rol inválido',
            ];
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'password' => 'min:8|max:16|confirmed',
                'image' => 'image',
            ], $messages);

            if (isset($validatedData['image'])) {
                $imageName = time().'.'.$request->image->extension();
                $request->image->move(public_path('images'), $imageName);
                $validatedData['img'] = asset('images/'.$imageName);
            }

            $user = $currentUser;

            foreach ($validatedData as $field => $value) {
                $user->$field = $value;
                if ($field === 'email') {
                    $user->$field = $user->email;
                }
                if ($field === 'country_id') {
                    $user->$field = $user->country_id;
                }
                if ($field === 'role_id') {
                    $user->$field = $user->role_id;
                }
                if ($field === 'allowed_currencies') {
                    $user->permissions = $user->permissions;
                }
            }
            $user->save();

            return response()->json(['message' => 'exito'], 201);
        }

        return response()->json(['message' => 'forbiden'], 401);

    }

    public function destroy($id)
    {
        $currentUser = User::find(auth()->user()->id);
        if (auth()->user()->id == $id) {
            return response()->json(['message' => 'forbiden'], 401);
        }
        if ($currentUser->role->id === 1) {
            $user = User::find($id);
            if ($user->is_initial) {
                return response()->json(['message' => 'forbiden'], 401);
            }
            $user->delete();

            return response()->json(['message' => 'exito'], 201);
        }

        return response()->json(['message' => 'forbiden'], 401);
    }

    public function getUserRoles()
    {
        $roles = Role::withCount(['users as count'])->get();

        return response()->json($roles, 200);
    }

    public function getBalances(Request $request)
    {
        $currentUser = User::find(auth()->user()->id);
        $search = $request->get('search');
        $per_page = $request->get('per_page', 10);
        $paginated = $request->get('paginated', 'yes');
        $order = $request->get('order', 'created_at');
        $orderBy = $request->get('order_by', 'desc');
        $role = $request->get('role');
        $country = $request->get('country');
        $more_than_one = $request->get('moreThanOne', 'no');

        $balances = UserBalance::query()
            ->leftjoin('users', 'users.id', '=', 'user_balances.user_id')
            ->leftjoin('currencies', 'currencies.id', '=', 'user_balances.currency_id')
            ->select('user_balances.*', 'users.name as user_name', 'currencies.name as currency_name')
            ->groupBy('user_balances.id');

        if ($currentUser->role->id !== 1) {
            $balances = $balances->where('users.id', $currentUser->id);
        }
        if ($search) {
            $balances = $balances
                ->select('user_balances.*', 'users.name as user_name', 'currencies.name as currency_name')
                ->where(function ($balances) use ($search) {
                    $balances->where('users.name', 'LIKE', "%{$search}%")
                        ->orWhere('currencies.name', 'LIKE', "%{$search}%")
                        ->orWhere('users.email', 'LIKE', "%{$search}%");
                });
        }
        if ($more_than_one === 'yes') {
            $balances = $balances->where('user_balances.balance', '>', 1);
        }
        if ($role) {
            $balances = $balances->where('users.role_id', $role);
        }
        if ($country) {
            $balances = $balances->where('users.country_id', $country);
        }
        if ($currentUser->role->id !== 1) {
            $balances = $balances->where('users.id', $currentUser->id);
        }
        if ($order) {
            $balances = $balances->orderBy("user_balances.$order", "$orderBy");
        }
        $balances = $balances->with('currency', 'user');
        if ($paginated === 'no') {
            return response()->json($balances->get(), 200);
        }

        return response()->json($balances->paginate($per_page), 200);
    }
}
