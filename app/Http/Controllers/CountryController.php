<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Movement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\search;

class CountryController extends Controller
{
    public function index(Request $request){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            
            $search = $request->get("search"); 
            $countries = Country::query()
                ->leftJoin("banks as b1", "b1.country_id", "=", "countries.id")
                ->leftJoin("currencies", "currencies.id", "countries.currency_id") // Cambia 'banks' por 'b1'
                ->select("countries.name as country_name", "countries.id as id_country", "countries.shortcode", "currencies.id as currency_id","currencies.name as currency_name", "currencies.symbol as currency_symbol", "currencies.shortcode as currency_shortcode")
                ->addSelect(DB::raw("IFNULL(sum(banks_accounts.balance), 0) as total"))
                ->leftJoin("banks_accounts", function($join) {
                    $join->on("b1.id", "=", "banks_accounts.bank_id"); // Cambia 'banks.id' por 'b1.id'
                })
                ->groupBy("country_name", "id_country", "shortcode", "currency_name", "currency_symbol", "currency_shortcode", "currency_id")->where("countries.delete", false);

            if ($search) {
                $countries = $countries->where('countries.name', 'LIKE', "%{$search}%");
            }
            $countries = $countries->where("countries.delete", false)->paginate(10);
            return response()->json([$countries], 200);

        }
    }
    public function store(Request $request){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $message = [
                'country_name.required' => 'El nombre del país es requerido',
                'country_shortcode.required' => 'El código de país es requerido',
            ];
            $validatedData = $request->validate([
                "country_name" => "required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/",
                "country_shortcode"=> "required|string|min:2|max:4",
                "currency_id" => "required|integer|exists:currencies,id"
            ], $message);
            $country = Country::create([
                "name"=> $validatedData['country_name'],
                "shortcode" => $validatedData["country_shortcode"],
                "config" => json_encode([]),
                "currency_id" => $validatedData["currency_id"]
            ]);
    
            if ($country) {
                return response()->json(['message' => 'exito'], 201);
            }
            else{
                return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 500);
            }

        }
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function show($id){
        return response()->json(Country::find($id), 200);
    }
    public function update(Request $request, $id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $message = [
                'country_name.required' => 'El nombre del país es requerido',
                'country_shortcode.required' => 'El código de país es requerido'
            ];
            $validatedData = $request->validate([
                "country_name" => "required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/",
                "country_shortcode"=> "required|string|min:2|max:4",
            ], $message);
            
            $country = Country::find($id);
            $country->name = $validatedData['country_name'];
            $country->shortcode = $validatedData["country_shortcode"];
            if ($country->save()) {
                return response()->json(['message' => 'exito'], 201);
            }
            else
            {
                return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 500);
            }
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function destroy($id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $country = Country::find($id);
            $country->delete = true;
            if($country->save()){
                // Obtén todos los bancos asociados a este país
                $banks = Bank::where('country_id', $id)->get();
                foreach ($banks as $bank) {
                    $bank->delete = true;
                    $bank->save();
                    // Obtén todas las cuentas bancarias asociadas a este banco
                    $bankAccounts = BankAccount::where('bank_id', $bank->id)->get();
                    foreach ($bankAccounts as $bankAccount) {
                        $bankAccount->delete = true;
                        $bankAccount->save();
                    }
                }
                return response()->json(['message' => 'exito'], 201);
            }
            
            return response()->json(['error'=> 'Hubo un problema al eliminar el pais'], 500);
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function getBanksCount(){
        return response()->json(Country::where('delete', false)->withCount(['banks as count'])->get(), 200);
    }
}
