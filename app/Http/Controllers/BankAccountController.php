<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\User;
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
        $bank_account = BankAccount::query()->where('banks_accounts.delete', false);
        
        if ($search) {
            $bank_account = $bank_account
                ->join('banks', 'banks_accounts.bank_id', '=', "banks.id")
                ->joint('users', 'user_id', "=", "users.id")
                ->select('banks_accounts.*', 'banks.name as bank_name', "user.name as user_name");

            $bank_account = $bank_account->where("banks.name", "LIKE", "%{$search}%")
                ->orWhere("banks_accounts.name", "LIKE", "%{$search}%")
                ->orWhere("banks_accounts.identifier", "LIKE", "%{$search}%")
                ->orWhere("users.name", "LIKE", "%{$search}%");
        }
        if ($paginated === 'no') {
            $bank_account = $bank_account->where('delete', false)->with('bank.country', 'bank.currency', 'user')->get();
        }
        else{
            $bank_account = $bank_account->where('delete', false)->with('bank.country', 'bank.currency', 'user')->paginate($per_page);
        }
        return response()->json($bank_account, 200);
    }
    public function create(){
    }
    public function store(Request $request){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $messages = [
                'name.required' => 'El nombre es un campo requerido',
                'identifier.required' => 'Identificador requerido, recuerde que este es lo que permite diferenciar entre cuentas, ejemplo el numero de cuenta o correo electronico',
                'bank.required' => 'Banco es requerido',
                'bank.exist' => 'Banco no existe',
                'balance.required' => 'Balance de la cuenta requerido',
                'balance.numeric' => 'Balance debe ser númerico',
                "user.required" => 'Usuario es requerido'
            ];
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'identifier'=> 'required|string|min:2|max:255',
                'bank' => 'required|exists:banks,id',
                'user' => 'required|exists:users,id'
            ], $messages);
            $bank_account = BankAccount::create([
                "name" => $validatedData['name'],
                "identifier" => $validatedData['identifier'],
                "bank_id" => $validatedData['bank'],
                "user_id" => $validatedData['user'],
                "balance" => 0.00,
                "meta_data" => json_encode([])
            ]);
            if ($bank_account) {
                return response()->json(['message' => 'exito'], 201);
            }
            else{
                return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 500);
            }
        }
        return response()->json(['message' => 'forbiden'], 401);
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
                'bank' => 'required|exists:banks,id',
                'user' => 'required|exists:users,id'
            ], $messages);
            $validatedData['bank_id'] = $validatedData['bank'];
            $validatedData['user_id'] = $validatedData['user'];
            $bank = BankAccount::find($id);
            foreach ($validatedData as $field => $value) {
                if ($field !== 'bank' && $field !== "user") {
                    $bank->$field = $value;
                }
            }
            $bank->save();
    
            return response()->json(['message'=> 'exito'], 201);
        }
        return response()->json(['message' => 'forbiden'], 401);
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
