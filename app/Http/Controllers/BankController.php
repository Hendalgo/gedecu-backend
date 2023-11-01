<?php

namespace App\Http\Controllers;

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
        if ($user->role->id === 1) {

            $search = $request->get("search"); 
            $country = $request->get("country");
            
            $bank = Bank::query()
                ->select("banks.id", "banks.name", "banks.meta_data", "banks.country_id")
                ->addSelect(DB::raw("IFNULL(sum(banks_accounts.balance), 0) as amount"))
                ->leftJoin("banks_accounts", function($join) {
                    $join->on("banks.id", "=", "banks_accounts.bank_id")
                        ->where('banks_accounts.delete', false);
                })
                ->groupBy("banks.id", "banks.name", "banks.meta_data", "banks.country_id");

            if ($search) {
                $bank = $bank->havingRaw('banks.name LIKE ? OR amount LIKE ?', ["%{$search}%", "%{$search}%"]);
            }

            if ($country) {
                $bank = $bank->where("banks.country_id", "=", $country);
            }

            $bank = $bank->with("country.currency");

            return response()->json($bank->paginate(10), 200);
        }
        else{
            return response()->json(Bank::with('country.currency')->paginate(10), 200);
        }
    }
    public function create(){
    }
    public function store(Request $request){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $message = [
                'name.required' => 'El nombre es requerido',
                'country.required' => 'El país es requerido',
            ];
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'image'=> 'image',
                'country_id' => 'required|exists:countries,id'
            ], $message);

            if (isset($validatedData['image'])) {
                $imageName = time().'.'.$request->image->extension();  
                $request->image->move(public_path('images'), $imageName);
                $validatedData['img'] = asset('images/'.$imageName);
            }
            $validatedData['meta_data'] = json_encode([
                'styles' =>[]
            ]);
            print_r($validatedData);

            $bank = Bank::create($validatedData);
    
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
        $bank = Bank::with('country.currency')->find($id);

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
            ];
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'img'=> 'image',
                'country_id' => 'required|exists:countries,id'
            ], $message);
            $bank = Bank::find($id);
            foreach ($validatedData as $field => $value) {
                $bank->$field = $value;
            }
            $bank->save();
    
            return response()->json(['message'=> 'exito'], 201);
        }
        return response()->json(['message' => 'forbiden'], 401);
    }
    public function destroy($id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $bank = Bank::find($id);

            if ($bank) {
                $bank->delete();
                return response()->json(['message' => 'exito'], 200);
            }
            else{
                return response()->json(['message' => 'error'], 404);
            }
        }
        return response()->json(['message' => 'forbiden'], 401);
    }
    public function getBanksTotal(){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $today = Carbon::today();
        
            $countries = Bank::join("countries", "banks.country_id", "=", "countries.id") ->join("banks_accounts", "banks.id", "=", "banks_accounts.bank_id") ->select("countries.name as country_name", "countries.id as id_country", "countries.shortcode", "currencies.symbol", DB::raw("sum(banks_accounts.balance) as total")) ->join("currencies", "countries.currency_id", "=", "currencies.id") ->groupBy("country_name", "id_country", "shortcode", "symbol") ->get();
                
            foreach ($countries as $country) {
                $sum = 0;

                $bankIds = Bank::where("country_id", $country->id_country)->pluck("id"); $bankAccounts = BankAccount::whereIn("bank_id", $bankIds)->get();
                foreach($bankAccounts as $bankAccount){
                    $lastMovement = Movement::where('bank_account_id', $bankAccount->id)
                        ->whereDate('created_at', '=', $today)
                        ->orderBy('created_at', 'asc')
                        ->first();
                    if ($lastMovement) {
                        $sum += $lastMovement->bank_amount;
                    }
                    
                }
                if ($sum != 0) {
                    $growth = round((($country->total - $sum ) / $sum) * 100, 7);
                } else {
                    $growth = $sum > 0 ? 100 : 0;
                }
                $country->total_before = $sum;
                $country->growth_percentage = $growth;
            }
        
            return response()->json([$countries], 200);
        }
        return response()->json(['message' => 'forbiden'], 401);
        
    }
}
