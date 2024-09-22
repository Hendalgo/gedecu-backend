<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Country;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $order = $request->get('order');
        $orderBy = $request->get('order_by', 'desc');
        $since = $request->get('since');
        $until = $request->get('until');
        $search = $request->get('search');
        $per_page = $request->get('per_page', 10);
        $paginated = $request->get('paginated', 'yes');
        $not_owner = $request->get('not_owner', 'no');
        $country = $request->get('country');

        $store = Store::leftjoin('banks_accounts', 'stores.id', '=', 'banks_accounts.store_id')
            ->leftjoin('currencies', 'banks_accounts.currency_id', '=', 'currencies.id')
            ->leftjoin('users', 'stores.user_id', '=', 'users.id')
            ->where('banks_accounts.account_type_id', 3)
            ->select(
                'stores.id',
                'stores.name',
                'stores.location',
                'stores.user_id',
                'stores.country_id',
                'stores.created_at',
                'stores.updated_at',
                'stores.delete',
                'banks_accounts.balance as cash_balance',
                'currencies.id as currency_id',
                'currencies.name as currency_name',
                'currencies.symbol as currency_symbol'
            )
            ->groupBy(
                'stores.id',
                'stores.name',
                'stores.location',
                'stores.user_id',
                'stores.country_id',
                'stores.created_at',
                'stores.updated_at',
                'stores.delete',
                'banks_accounts.balance',
                'currencies.id',
                'currencies.name',
                'currencies.symbol'
            )
            ->orderBy($order ?? 'stores.id', $orderBy ?? 'desc');

        if ($since) {
            $store = $store->whereDate('stores.created_at', '>=', $since);
        }
        if ($until) {
            $store = $store->whereDate('stores.created_at', '<=', $until);
        }
        if ($search) {
            $store = $store->where(function ($query) use ($search) {
                $query->where('stores.name', 'LIKE', "%{$search}%")
                    ->orWhere('stores.location', 'LIKE', "%{$search}%")
                    ->orWhere('users.name', 'LIKE', "%{$search}%");
            });
        }
        if ($country) {
            $store = $store->where('stores.country_id', '=', $country);
        }
        if ($not_owner === 'yes') {
            $store = $store->where(function ($query) {
                $query->where('stores.user_id', '!=', auth()->user()->id)
                    ->orWhereNull('stores.user_id');
            });
        }
        $store = $store->with('accounts', 'user');
        if ($paginated === 'no') {
            return response()->json($store->where('stores.delete', false)->get(), 200);
        }

        return response()->json($store->where('stores.delete', false)->paginate($per_page), 200);
    }

    public function show($id)
    {
        $user = User::find(auth()->user()->id);
        $store = Store::where('id', '=', $id)->where('delete', false);

        if ($user->role->id === 1) {
            $store = $store->with(['accounts' => function ($query) {
                $query->where('account_type_id', 3)->with('currency');
            }, 'user'])->first();

            if (! $store) {
                return response()->json(['message' => 'No se encontro el local'], 404);
            }

            $bank_accounts = BankAccount::where('store_id', '=', $id)->where('account_type_id', '!=', 3)->with('currency', 'type', 'bank')->get();
            $cash_account = BankAccount::where('store_id', '=', $id)->where('account_type_id', '=', 3)->with('currency', 'type')->first();
            $auxStore = $store->toArray();

            $auxStore['accounts'] = $bank_accounts;
            $auxStore['cash_balance'] = $cash_account;

            return response()->json($auxStore, 200);
        }
        $store = $store->where('user_id', '=', $user->id)->with(['accounts' => function ($query) {
            $query->where('account_type_id', 3)->with('currency');
        }, 'user'])->first();

        if (! $store) {
            return response()->json(['message' => 'No se encontro el local'], 404);
        }

        $bank_accounts = BankAccount::where('store_id', '=', $id)->where('account_type_id', '!=', 3)->with('currency', 'type', 'bank')->get();
        $cash_account = BankAccount::where('store_id', '=', $id)->where('account_type_id', '=', 3)->with('currency', 'type')->first();

        $auxStore = $store->toArray();

        $auxStore['accounts'] = $bank_accounts;
        $auxStore['cash_balance'] = $cash_account;

        return response()->json($auxStore, 200);
    }

    public function create()
    {
    }
    
    public function store(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $messages = [
                'name.required' => 'El nombre es un campo requerido',
                'location.required' => 'Dirección requerida',
                'user_id.required' => 'Usuario requerido',
                'user_id.exist' => 'Usuario no existe',
                'user_id.user_role' => 'Usuario no es un encargado de tienda',
                'country.required' => 'País requerido',
                'country.exist' => 'País no existe',
                'balance.required' => 'Balance requerido',
                'balance.numeric' => 'Balance debe ser un número',
                'currency_id.required' => 'Moneda requerida',
                'currency_id.exists' => 'Moneda no existe',
            ];
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'location' => 'required|string|min:2|max:255',
                'user_id' => 'required|exists:users,id|user_role:3',
                'country_id' => 'required|exists:countries,id',
                'balance' => 'required|numeric',
                'currency_id' => 'required|exists:currencies,id',
            ], $messages);
            $exist_user = Store::where('user_id', '=', $validatedData['user_id'])->first();
            if ($exist_user) {
                $exist_user->user_id = null;
                $exist_user->save();
            }
            $store = null;
            $bank_account = null;
            //Get currency from store country
            try {
                DB::transaction(function () use ($validatedData, &$store, &$bank_account) {
                    $user = Store::where('user_id', '=', $validatedData['user_id'])->first();
                    if ($user) {
                        $user->user_id = null;
                        $user->save();
                    }
                    $store = Store::create([
                        'name' => $validatedData['name'],
                        'location' => $validatedData['location'],
                        'user_id' => $validatedData['user_id'],
                        'country_id' => $validatedData['country_id'],
                    ]);
                    $bank_account = $store->accounts()->create([
                        'name' => 'Efectivo',
                        'identifier' => 'Efectivo',
                        'balance' => $validatedData['balance'],
                        'currency_id' => $validatedData['currency_id'],
                        'account_type_id' => 3,
                    ]);
                });

                return response()->json([$store, $bank_account], 201);
            } catch (\Throwable $th) {
                return response()->json(['error' => $th->getMessage()], 500);
            }
        }

        return response()->json(['message' => 'forbiden'], 401);
    }

    public function update(Request $request, $id)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'location' => 'required|string|min:2|max:255',
                'user_id' => 'required|exists:users,id',
            ]);
            $Store = Store::find($id);

            $exist_user = Store::where('user_id', '=', $validatedData['user_id'])->where('id', '!=', $id)->first();
            if ($exist_user) {
                $exist_user->user_id = null;
                $exist_user->save();
            }
            foreach ($validatedData as $field => $value) {
                $Store->$field = $value;
            }
            $Store->save();

            return response()->json(['message' => 'exito'], 201);
        }

        return response()->json(['message' => 'forbiden'], 401);
    }

    public function destroy($id)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $Store = Store::find($id);
            $Store->delete();

            return response()->json(['message' => 'exito'], 201);
        }

        return response()->json(['message' => 'forbiden'], 401);
    }
}
