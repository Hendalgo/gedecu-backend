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
        $paginated = $request->get('paginated', 'yes');
        $per_page = $request->get('per_page', 10);
        if ($user->role->id === 1) {

            $search = $request->get("search"); 
            $country = $request->get("country");
            
            $bank = Bank::query()
                ->select("banks.id", "banks.name", "banks.meta_data", "banks.country_id", 'banks.currency_id')
                ->addSelect(DB::raw("IFNULL(sum(banks_accounts.balance), 0) as amount"))
                ->leftJoin("banks_accounts", function($join) {
                    $join->on("banks.id", "=", "banks_accounts.bank_id")
                        ->where('banks_accounts.delete', false);
                })
                ->groupBy("banks.id", "banks.name", "banks.meta_data", "banks.country_id", 'banks.currency_id');

            if ($search) {
                $bank = $bank->havingRaw('banks.name LIKE ? OR amount LIKE ?', ["%{$search}%", "%{$search}%"]);
            }

            if ($country) {
                $bank = $bank->where("banks.country_id", "=", $country);
            }

            $bank = $bank->where('banks.delete', false)->with("country", "currency");

            if ($paginated === 'no') {
                return response()->json($bank->get(), 200);
            }
            return response()->json($bank->paginate($per_page), 200);
        }
        else{
            $bank = Bank::where('banks.delete', false)->with('country', 'currency');
            if ($paginated === 'no') {
                return response()->json($bank->get(), 200);
            }
            return response()->json($bank->paginate($per_page), 200);
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
                'currency.required' => 'La moneda es requerida',
            ];
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'image'=> 'image',
                'country' => 'required|exists:countries,id',
                'currency' => 'required|exists:currencies,id'
            ], $message);
            $validatedData['meta_data'] = json_encode([
                'styles' =>[]
            ]);

            $bank = Bank::create([
                'name' => $validatedData['name'],
                'country_id' => $validatedData['country'],
                'currency_id' => $validatedData['currency'],
                'meta_data' => $validatedData['meta_data']
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
        $bank = Bank::with('country', 'currency')->find($id);

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
                'currency' => 'required|exists:currencies,id'
            ], $message);
            $bank = Bank::find($id);
            /* foreach ($validatedData as $field => $value) {
                $bank->$field = $value;
            } */
            $bank->name = $validatedData['name'];
            $bank->country_id = $validatedData['country'];
            $bank->currency_id = $validatedData['currency'];

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
    public function getBanksTotal(){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $today = Carbon::today();
        
            $countries = Bank::join("countries", "banks.country_id", "=", "countries.id")
            ->join("banks_accounts", "banks.id", "=", "banks_accounts.bank_id")
            ->join("currencies", "banks.currency_id", "=", "currencies.id")
            ->select("countries.name as country_name", "countries.id as id_country", "countries.shortcode", "currencies.symbol", "currencies.id as id_currency", "currencies.shortcode as currency_shortcode", DB::raw("sum(banks_accounts.balance) as total"))
            ->groupBy("country_name", "id_country", "shortcode", "symbol", "id_currency", "currency_shortcode")
            ->where('banks_accounts.delete',false)
            ->where('banks.delete', false)
            ->where('countries.delete',false)
            ->get();

                
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
