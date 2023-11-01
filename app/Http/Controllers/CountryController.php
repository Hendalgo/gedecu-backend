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
            $countries = Bank::query()
                ->join("countries", "banks.country_id", "=", "countries.id")
                ->select("countries.name as country_name", "countries.id as id_country", "countries.shortcode", "currencies.symbol", 'currencies.name as currency_name', "currencies.shortcode as currency_shortcode")
                ->addSelect(DB::raw("IFNULL(sum(banks_accounts.balance), 0) as total"))
                ->leftJoin("banks_accounts", function($join) {
                    $join->on("banks.id", "=", "banks_accounts.bank_id");
                })
                ->join("currencies", "countries.currency_id", "=", "currencies.id")
                ->groupBy("country_name", "id_country", "shortcode", "symbol", "currency_name", "currency_shortcode")->where("countries.delete", false);

            if ($search) {
                $countries = $countries->where('countries.name', 'LIKE', "%{$search}%") 
                    ->orWhere("currencies.name", "LIKE", "%{$search}%")
                    ->orWhere("currencies.shortcode", "LIKE", "%{$search}%");
            }
            $countries = $countries->paginate(10);
            return response()->json([$countries], 200);
        }
    }
    public function store(Request $request){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $message = [
                'country_name.required' => 'El nombre del país es requerido',
                'country_shortcode.required' => 'El código de país es requerido',
                'currency_symbol.numeric' => 'El símbolo de la moneda es requerido',
                'currency_shortcode.required' => 'El código de la moneda es requerido',
                "currency_name.required" => "Nombre de la moneda es requerido"
            ];
            $validatedData = $request->validate([
                'currency_name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'currency_shortcode'=> 'required|string|min:2|max:4',
                'currency_symbol' => 'required',
                "country_name" => "required",
                "country_shortcode"=> "required"
            ], $message);
            $currency = Currency::create([
                "name"=> $validatedData['currency_name'],
                "shortcode" => $validatedData["currency_shortcode"],
                "symbol" => $validatedData["currency_symbol"]
            ]);
            if($currency){
                $country = Country::create([
                    "name"=> $validatedData['country_name'],
                    "shortcode" => $validatedData["country_shortcode"],
                    "currency_id" => $currency->id ,
                    "config" => json_encode([])
                ]);
        
                if ($country) {
                    return response()->json(['message' => 'exito'], 201);
                }
                else{
                    return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 500);
                }
            }
            else
            {
                return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 500);
            }
            /* if ($validatedData['image']) {
                $imageName = time().'.'.$request->image->extension();  
                $request->image->move(public_path('images'), $imageName);
                $validatedData['img'] = asset('images/'.$imageName);
            } */

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
                'country_shortcode.required' => 'El código de país es requerido',
                'currency_symbol.numeric' => 'El símbolo de la moneda es requerido',
                'currency_shortcode.required' => 'El código de la moneda es requerido',
                "currency_name.required" => "Nombre de la moneda es requerido"
            ];
            $validatedData = $request->validate([
                'currency_name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'currency_shortcode'=> 'required|string|min:2|max:4',
                'currency_symbol' => 'required',
                "country_name" => "required",
                "country_shortcode"=> "required"
            ], $message);
            
            $country = Country::find($id);
            $country->name = $validatedData['country_name'];
            $country->shortcode = $validatedData["country_shortcode"];
            if($country->save()){
        
                $currency = Currency::find($country->currency_id);
                $currency->name =  $validatedData['currency_name'];
                $currency->shortcode = $validatedData["currency_shortcode"];
                $currency->symbol = $validatedData["currency_symbol"];
    
                if ($currency->save()) {
                    return response()->json(['message' => 'exito'], 201);
                }
                else{
                    return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 500);
                }
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
                return response()->json(['message' => 'exito'], 201);
            }
            
            return response()->json(['error'=> 'Hubo un problema al eliminar el pais'], 500);
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function getBanksCount(){
        return response()->json(Country::withCount(['banks as count'])->get(), 200);
    }
}
