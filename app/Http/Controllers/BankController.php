<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Movement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    public function index(Request $request){
        $user = User::find(auth()->user()->id);
        $paginated = $request->get('paginated', 'yes');
        $per_page = $request->get('per_page', 10);
        $search = $request->get("search"); 
        $country = $request->get("country");
        $type = $request->get("type");
        
        $bank = Bank::where('banks.delete', false)
            ->leftjoin('accounts_types', 'banks.type_id', '=', 'accounts_types.id')
            ->select('banks.*');
        /* if ($search) {
            $bank = $bank->havingRaw('banks.name LIKE ? OR amount LIKE ?', ["%{$search}%", "%{$search}%"]);
        } */
        if ($search){
            $bank = $bank->where(function ($query) use ($search){
                $query->where('banks.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('banks.meta_data', 'LIKE', '%'.$search.'%')
                    ->orWhere('accounts_types.name', 'LIKE', '%'.$search.'%');
            });
        }
        if ($country) {
            $bank = $bank->where("banks.country_id", "=", $country);
        }
        if ($type) {
            $bank = $bank->where("banks.type_id", "=", $type);
        }
        $bank = $bank->with("country", "type");
        if ($paginated === 'no') {
            return response()->json($bank->get(), 200);
        }
        return response()->json($bank->paginate($per_page), 200);
    }
    public function create(){
    }
    public function store(Request $request){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $message = [
                'name.required' => 'El nombre es requerido',
                'country.required' => 'El país es requerido',
                'type_id.required' => 'El tipo de cuenta es requerido',
            ];
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'image'=> 'image',
                'country' => 'required|exists:countries,id',
                'type_id' => 'required|exists:accounts_types,id'
            ], $message);
            $validatedData['meta_data'] = json_encode([
                'styles' =>[]
            ]);

            $bank = Bank::create([
                'name' => $validatedData['name'],
                'country_id' => $validatedData['country'],
                'meta_data' => $validatedData['meta_data'],
                'type_id' => $validatedData['type_id']
            ]);
    
            if ($bank) {
                return response()->json(['message' => 'exito'], 201);
            }
            else{
                return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 500);
            }
        }
        return response()->json(['message' => 'forbiden'], 401);
    }
    public function show(Request $request, $id){
        
        $user = User::find(auth()->user()->id);
        $bank = Bank::with('country')->find($id);

        return response()->json($bank, 200);
    }
    public function update(Request $request, $id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $message = [
                'name.required' => 'El nombre es requerido',
                'amount.required' => 'El monto es requerido',
                'amount.numeric' => 'El monto debe ser un valor numérico',
                'country.required' => 'El país es requerido',
                'currency.required' => 'La moneda es requerida',
            ];
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'country' => 'required|exists:countries,id',
                'type_id' => 'required|exists:accounts_types,id',                
            ], $message);
            
            $bank = Bank::find($id);
            /* foreach ($validatedData as $field => $value) {
                $bank->$field = $value;
            } */
            
            $bank->name = $validatedData['name'];
            $bank->country_id = $validatedData['country'];
            $bank->type_id = $validatedData['type_id'];
            $bank->save();
            return response()->json(['message'=> 'exito'], 201);
        }
        return response()->json(['message' => 'forbiden'], 401);
    }
    public function destroy($id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $bank = Bank::find($id);
            $bank->delete = true;
            $bank->save();
            $bankAccounts = BankAccount::where('bank_id', $bank->id)->get();
            foreach ($bankAccounts as $bankAccount) {
                $bankAccount->delete = true;
                $bankAccount->save();
            }
            return response()->json(['message' => 'exito'], 201);
        }
        return response()->json(['message' => 'forbiden'], 401);
    }
}
