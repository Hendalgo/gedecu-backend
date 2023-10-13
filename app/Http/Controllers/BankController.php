<?php

namespace App\Http\Controllers;

use App\Models\Bank;
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
            $bank = Bank::with('country.currency');
            
            $search = $request->get('search');
            $country = $request->get('country');
            $bank->when($search, function ($query, $search){
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('amount', 'LIKE', "%{$search}%")
                    ->orWhereHas('country', function ($query) use ($search){
                        $query->where('name', 'LIKE', "%{$search}%");
                    });
            });
            if ($country) {
                $bank->where('country_id', '=', $country);
            }
            return response()->json($bank->paginate(10), 200);
        }
        else{
                                                    //->select('id', 'name', 'img', 'country_id', 'created_at', 'updated_at')
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
                'amount.required' => 'El monto es requerido',
                'amount.numeric' => 'El monto debe ser un valor numérico',
                'country.required' => 'El país es requerido',
            ];
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'amount'=> 'required|numeric',
                'image'=> 'image',
                'country_id' => 'required|exists:countries,id'
            ], $message);

            if (isset($validatedData['image'])) {
                $imageName = time().'.'.$request->image->extension();  
                $request->image->move(public_path('images'), $imageName);
                $validatedData['img'] = asset('images/'.$imageName);
            }
            $validatedData['config'] = json_encode([
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
        return response()->json(['message' => 'forbiden', 401]);
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
                'amount'=> 'required|numeric',
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
        return response()->json(['message' => 'forbiden', 401]);
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
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function getBanksTotal(){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
        
            $banks = Bank::join('countries', 'banks.country_id', '=', 'countries.id')
            ->select('countries.name as country_name', 'countries.id as id_country', 'countries.shortcode', 'currencies.symbol', DB::raw('sum(banks.amount) as total'))
            ->join('currencies', 'countries.currency_id', '=', 'currencies.id')
            ->groupBy('country_name', 'id_country', 'shortcode', 'symbol')
            ->get();
                
            foreach ($banks as $bank) {
                if (isset($bank->total)) {
                    $totalToday = $bank->total;
                } else {
                    $totalToday = 0;
                }
        
                $totalYesterday = Movement::where('bank_id', $bank->id)
                    ->whereDate('created_at', $yesterday)
                    ->sum('amount');
        
                if ($totalYesterday != 0) {
                    $growthPercentage = (($totalToday - $totalYesterday) / $totalYesterday) * 100;
                } else {
                    $growthPercentage = 100;
                }
        
                $bank->total = $totalToday;
                $bank->growth_percentage = $growthPercentage;
            }
        
            return response()->json([$banks], 200);
        }
        return response()->json(['message' => 'forbiden', 401]);
        
    }
}
