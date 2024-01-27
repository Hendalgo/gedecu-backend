<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index(Request $request){
        $order = $request->get('order');
        $orderBy = $request->get('order_by', 'desc');
        $since = $request->get('since');
        $until = $request->get('until');
        $search = $request->get('search');
        $paginated = $request->get('paginated', 'yes');
        $per_page = $request->get('per_page', 10);
        $bank = $request->get('bank');
        $store = $request->get('store');
        $currency = $request->get('currency');
        $user = User::find(auth()->user()->id);

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
        ]);
        $bank_account = BankAccount::where('banks_accounts.delete', false)
                ->where('banks_accounts.account_type_id', "!=", 3)
                ->leftjoin('banks', 'banks_accounts.bank_id', '=', "banks.id")
                ->leftjoin('countries', 'banks.country_id', '=', "countries.id")
                ->leftjoin('users', 'user_id', "=", "users.id")
                ->leftjoin('stores', 'banks_accounts.store_id', '=', "stores.id")
                ->select('banks_accounts.*');
        $country = $request->get('country');
        $type = $request->get('type');
        
        if ($search) {
            $bank_account = $bank_account->where(function ($query) use ($search){
                $query->where('banks_accounts.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('banks_accounts.identifier', 'LIKE', '%'.$search.'%')
                    ->orWhere('banks.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('countries.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('users.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('stores.name', 'LIKE', '%'.$search.'%');
            });
        }
        if ($bank) {
            $bank_account = $bank_account->where('bank_id', $bank);
        }
        if($type){
            $bank_account = $bank_account->where('type_id', $type);
        }
        if ($country) {
            $bank_account = $bank_account->where('countries.id', $country);
        }
        if ($currency) {
            $bank_account = $bank_account->where('banks_accounts.currency_id', $currency);
        }
        if ($store) {
            $bank_account = $bank_account->where('banks_accounts.store_id', $store);
        }
        if ($since) {
            $bank_account = $bank_account->whereDate('banks_accounts.created_at', '>=', $since);
        }
        if ($until) {
            $bank_account = $bank_account->whereDate('banks_accounts.created_at', '<=', $until);
        }
        if ($order) {
            $bank_account = $bank_account->orderBy('banks_accounts.'.$order, $orderBy);
        }
        else{
            $bank_account = $bank_account->orderBy('banks_accounts.created_at', 'desc');
        }
        if($user->role->id === 3){
            $bank_account = $bank_account->where('banks_accounts.store_id', $user->store->id);
        }
        if ($user->role->id === 2) {
            $bank_account = $bank_account->where('banks_accounts.user_id', $user->id);
        }
        if($user->role->id === 4 || $user->role->id === 5 || $user->role->id === 6){
            return response()->json(['message' => 'No tiene acceso a estas cuentas'], 403);
        }
        if ($paginated === 'no') {
            $bank_account = $bank_account->with('bank.country', 'bank.type', 'currency', 'user', 'store.user')->get();
        }
        else{
            $bank_account = $bank_account->with('bank.country', 'bank.type','currency', 'user', 'store.user')->paginate($per_page);
        }
        return response()->json($bank_account, 200);
    }
    public function create(){
    }
    public function store(Request $request){
        $user = User::find(auth()->user()->id);
        $messages = [
            'name.required' => 'El nombre es un campo requerido',
            'identifier.required' => 'Identificador requerido, recuerde que este es lo que permite diferenciar entre cuentas, ejemplo el numero de cuenta o correo electronico',
            'bank.required' => 'Banco es requerido',
            'bank.exist' => 'Banco no existe',
            'balance.required' => 'Balance de la cuenta requerido',
            'balance.numeric' => 'Balance debe ser númerico',
            "user.required" => 'Usuario es requerido',
            "user.exist" => 'Usuario no existe'
        ];
        
        if ($user->role->id === 1) {
            /* $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'identifier'=> 'required|string|min:2|max:255',
                'bank' => 'required|exists:banks,id',
                'user' => 'required|exists:users,id',
                'balance' => 'required|numeric',
                'currency_id' => 'required|exists:currencies,id',
            ], $messages);
            $bank_type = Bank::find($validatedData['bank'])->type_id;
            $bank_account = BankAccount::create([
                "name" => $validatedData['name'],
                "identifier" => $validatedData['identifier'],
                "bank_id" => $validatedData['bank'],
                "user_id" => $validatedData['user'],
                "balance" => $validatedData['balance'],
                "account_type_id" => $validatedData['account_type_id'],
                "meta_data" => json_encode([]),
                'currency_id' => $validatedData['currency_id'],
                'account_type_id' => $bank_type === 1 ? 1 : 2,
            ]);
            if ($bank_account) {
                return response()->json($bank_account, 201);
            }
            else{
                return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 500);
            } */
            return response()->json(['message' => 'forbiden'], 403);
        }
        else{
            if ($user->role->id === 3) {
                $validatedData = $request->validate([
                    'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                    'identifier'=> 'required|string|min:2|max:255',
                    'bank_id' => 'required|exists:banks,id',
                    'balance' => 'required|numeric',
                    'currency_id' => 'required|exists:currencies,id',
                ], $messages);
                $validator = Validator::make([], []);
                $validator->setData(['user', $user->id]);
                $validator->setRules([
                    'user' => 'required|exists:users,id|user_has_store',
                ]);
                $bank_type = Bank::find($validatedData['bank_id'])->type_id;
                $bank_account = BankAccount::create([
                    "name" => $validatedData['name'],
                    "identifier" => $validatedData['identifier'],
                    "bank_id" => $validatedData['bank_id'],
                    "store_id" => $user->store->id,
                    "balance" => $validatedData['balance'],
                    "meta_data" => json_encode([]),
                    "currency_id" => $validatedData['currency_id'],
                    'account_type_id' => $bank_type == 1 ? 1 : 2,
                ]);
                return response()->json($bank_account, 201);
            }
            else{
                $validatedData = $request->validate([
                    'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                    'identifier'=> 'required|string|min:2|max:255',
                    'bank_id' => 'required|exists:banks,id',
                    'balance' => 'required|numeric',
                    'currency_id' => 'required|exists:currencies,id',
                ], $messages);

                $bank_type = Bank::find($validatedData['bank_id'])->type_id;
                $bank_account = BankAccount::create([
                    "name" => $validatedData['name'],
                    "identifier" => $validatedData['identifier'],
                    "bank_id" => $validatedData['bank_id'],
                    "user_id" => $user->id,
                    "balance" => $validatedData['balance'],
                    "meta_data" => json_encode([]),
                    "currency_id" => $validatedData['currency_id'],
                    'account_type_id' => $bank_type == 1 ? 1 : 2,
                ]);
                return response()->json($bank_account, 201);
            }
        }
        return response()->json(['message' => 'errors'], 500);
    }
    public function show($id){
        return response()->json(BankAccount::find($id), 200);
    }
    public function update(Request $request, $id){
        $user = User::find(auth()->user()->id);
            
        if ($user->role->id === 1) {
            $messages = [
                'name.required' => 'El nombre es un campo requerido',
                'identifier.required' => 'Identificador requerido, recuerde que este es lo que permite diferenciar entre cuentas, ejemplo el numero de cuenta o correo electronico',
                'bank.required' => 'Banco es requerido',
                'bank.exist' => 'Banco no existe',
                'balance.required' => 'Balance de la cuenta requerido',
                'balance.numeric' => 'Balance debe ser númerico',
                'user.required' => 'El usuario es requerido'
            ];
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'identifier'=> 'required|string|min:2|max:255',
                'bank_id' => 'required|exists:banks,id',
                'currency_id' => 'required|exists:currencies,id',
            ], $messages);
            $bank = BankAccount::find($id);
            foreach ($validatedData as $field => $value) {
                if ($field !== 'bank' && $field !== "user") {
                    $bank->$field = $value;
                }
            }
            $bank->save();
    
            return response()->json($bank, 201);
        }
        else{
            $messages = [
                'name.required' => 'El nombre es un campo requerido',
                'identifier.required' => 'Identificador requerido, recuerde que este es lo que permite diferenciar entre cuentas, ejemplo el numero de cuenta o correo electronico',
                'bank.required' => 'Banco es requerido',
                'bank.exist' => 'Banco no existe',
                'balance.required' => 'Balance de la cuenta requerido',
                'balance.numeric' => 'Balance debe ser númerico',
            ];
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'identifier'=> 'required|string|min:2|max:255',
                'bank_id' => 'required|exists:banks,id',
                'currency_id' => 'required|exists:currencies,id',
            ], $messages);
            $bank = BankAccount::find($id);
            if ($bank->user_id !== $user->id) {
                return response()->json(['message' => 'forbiden'], 401);
            }
            foreach ($validatedData as $field => $value) {
                if ($field !== 'bank' && $field !== "user") {
                    $bank->$field = $value;
                }
            }
            $bank->save();
    
            return response()->json($bank, 201);
        }
    }
    public function destroy($id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $bank = BankAccount::find($id);
            $bank->delete = true; 
            $bank->save();
    
            return response()->json(['message'=> 'exito'], 201);
        }
        return response()->json(['message' => 'forbiden'], 401);
    }
}
