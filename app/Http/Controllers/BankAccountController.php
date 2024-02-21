<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'order' => 'in:balance,created_at',
            'order_by' => 'in:asc,desc',
            'since' => 'date',
            'until' => 'date',
            'search' => 'string',
            'paginated' => 'in:yes,no',
            'per_page' => 'integer',
            'bank' => 'integer|exists:banks,id',
            'store' => 'integer|exists:stores,id',
            'currency' => 'integer|exists:currencies,id',
            'country' => 'integer|exists:countries,id',
            'type' => 'integer|exists:types,id',
            'negatives' => 'in:yes,no',
            'user' => 'integer|exists:users,id',
        ]);

        $user = User::find(auth()->user()->id);

        if ($user->role->id === 5 || $user->role->id === 6) {
            return response()->json(['message' => 'No tiene acceso a estas cuentas'], 403);
        }

        $bank_account = BankAccount::where('banks_accounts.delete', false)
            ->where('banks_accounts.account_type_id', '!=', 3)
            ->leftjoin('banks', 'banks_accounts.bank_id', '=', 'banks.id')
            ->leftjoin('countries', 'banks.country_id', '=', 'countries.id')
            ->leftjoin('users', 'user_id', '=', 'users.id')
            ->leftjoin('stores', 'banks_accounts.store_id', '=', 'stores.id')
            ->select('banks_accounts.*');

        if ($validatedData['search']) {
            $bank_account = $bank_account->where(function ($query) use ($validatedData) {
                $query->where('banks_accounts.name', 'LIKE', '%'.$validatedData['search'].'%')
                    ->orWhere('banks_accounts.identifier', 'LIKE', '%'.$validatedData['search'].'%')
                    ->orWhere('banks.name', 'LIKE', '%'.$validatedData['search'].'%')
                    ->orWhere('countries.name', 'LIKE', '%'.$validatedData['search'].'%')
                    ->orWhere('users.name', 'LIKE', '%'.$validatedData['search'].'%')
                    ->orWhere('stores.name', 'LIKE', '%'.$validatedData['search'].'%');
            });
        }

        $filters = [
            'negatives' => ['banks_accounts.balance', '<', 0],
            'bank' => ['bank_id', '=', $validatedData['bank']],
            'type' => ['type_id', '=', $validatedData['type']],
            'country' => ['countries.id', '=', $validatedData['country']],
            'currency' => ['banks_accounts.currency_id', '=', $validatedData['currency']],
            'store' => ['banks_accounts.store_id', '=', $validatedData['store']],
            'since' => ['banks_accounts.created_at', '>=', $validatedData['since']],
            'until' => ['banks_accounts.created_at', '<=', $validatedData['until']],
            'user' => ['banks_accounts.user_id', '=', $validatedData['user']],
        ];

        foreach ($filters as $key => $value) {
            if ($validatedData[$key]) {
                $bank_account = $bank_account->where($value[0], $value[1], $value[2]);
            }
        }

        if ($user->role->id === 3) {
            $bank_account = $bank_account->where('banks_accounts.store_id', $user->store->id);
        }

        if ($user->role->id === 2) {
            $bank_account = $bank_account->where('banks_accounts.user_id', $user->id);
        }

        $order = $validatedData['order'] ?? 'created_at';
        $orderBy = $validatedData['order_by'] ?? 'desc';
        $bank_account = $bank_account->orderBy('banks_accounts.'.$order, $orderBy);

        if ($validatedData['paginated'] === 'no') {
            $bank_account = $bank_account->with('bank.country', 'bank.type', 'currency', 'user', 'store.user')->get();
        } else {
            $bank_account = $bank_account->with('bank.country', 'bank.type', 'currency', 'user', 'store.user')->paginate($validatedData['per_page'] ?? 10);
        }

        return response()->json($bank_account, 200);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $messages = [
            'name.required' => 'El nombre es un campo requerido',
            'identifier.required' => 'Identificador requerido, recuerde que este es lo que permite diferenciar entre cuentas, ejemplo el numero de cuenta o correo electronico',
            'bank.required' => 'Banco es requerido',
            'bank.exist' => 'Banco no existe',
            'balance.required' => 'Balance de la cuenta requerido',
            'balance.numeric' => 'Balance debe ser númerico',
            'user.required' => 'Usuario es requerido',
            'user.exist' => 'Usuario no existe',
        ];

        $commonRules = [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
            'identifier' => 'required|string|min:2|max:255',
            'bank_id' => 'required|exists:banks,id',
            'balance' => 'required|numeric',
            'currency_id' => 'required|exists:currencies,id',
        ];

        if ($user->role->id === 1) {
            $rules = $commonRules + [
                'account_type' => 'required|in:1,2',
            ];
            $validatedData = $request->validate($rules, $messages);

            $data = [
                'name' => $validatedData['name'],
                'identifier' => $validatedData['identifier'],
                'bank_id' => $validatedData['bank_id'],
                'balance' => $validatedData['balance'],
                'account_type_id' => $validatedData['account_type'],
                'meta_data' => json_encode([]),
                'currency_id' => $validatedData['currency_id'],
            ];

            if ($validatedData['account_type'] == 1) {
                $request->validate(['user_id' => 'required|exists:users,id|user_role:2']);
                $data['user_id'] = $request->user_id;
            } else {
                $request->validate(['store_id' => 'required|exists:stores,id']);
                $data['store_id'] = $request->store_id;
            }
        } else {
            $validatedData = $request->validate($commonRules, $messages);
            $data = [
                'name' => $validatedData['name'],
                'identifier' => $validatedData['identifier'],
                'bank_id' => $validatedData['bank_id'],
                'balance' => $validatedData['balance'],
                'meta_data' => json_encode([]),
                'currency_id' => $validatedData['currency_id'],
                'account_type_id' => Bank::find($validatedData['bank_id'])->type_id == 1 ? 1 : 2,
            ];

            if ($user->role->id === 3) {
                Validator::make(['user' => $user->id], ['user' => 'required|exists:users,id|user_has_store'])->validate();
                $data['store_id'] = $user->store->id;
            } else {
                $data['user_id'] = $user->id;
            }
        }

        $bank_account = BankAccount::create($data);

        if ($bank_account) {
            return response()->json($bank_account, 201);
        }

        return response()->json(['error' => 'Hubo un problema al crear el banco'], 500);
    }

    public function show($id)
    {
        return response()->json(BankAccount::find($id), 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find(auth()->user()->id);
        $bank = BankAccount::find($id);

        $messages = [
            'name.required' => 'El nombre es un campo requerido',
            'identifier.required' => 'Identificador requerido, recuerde que este es lo que permite diferenciar entre cuentas, ejemplo el numero de cuenta o correo electronico',
            'bank.required' => 'Banco es requerido',
            'bank.exist' => 'Banco no existe',
            'balance.required' => 'Balance de la cuenta requerido',
            'balance.numeric' => 'Balance debe ser númerico',
        ];

        if ($user->role->id === 1) {
            $messages['user.required'] = 'El usuario es requerido';
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
            'identifier' => 'required|string|min:2|max:255',
            'bank_id' => 'required|exists:banks,id',
            'currency_id' => 'required|exists:currencies,id',
        ], $messages);

        if ($user->role->id !== 1 && $bank->user_id !== $user->id) {
            return response()->json(['message' => 'forbiden'], 401);
        }

        foreach ($validatedData as $field => $value) {
            if ($field !== 'bank' && $field !== 'user') {
                $bank->$field = $value;
            }
        }

        $bank->save();

        return response()->json($bank, 201);
    }

    public function destroy($id)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $bank = BankAccount::find($id);
            $bank->delete();

            return response()->json(['message' => 'exito'], 201);
        } else {
            $user = User::find(auth()->user()->id);
            $bank = BankAccount::with('store')->find($id);

            if ($bank->store_id === $user->store->id || $bank->user_id === $user->id) {
                $bank->delete();

                return response()->json(['message' => 'exito'], 201);
            }

            return response()->json(['message' => 'forbidden'], 401);
        }

        return response()->json(['message' => 'forbiden'], 401);
    }
}
