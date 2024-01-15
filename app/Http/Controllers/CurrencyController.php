<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(Request $request){
        $user = User::find(auth()->user()->id);
        $search = $request->get('search');
        $paginated = $request->get('paginated', 'yes');
        $per_page = $request->get('per_page', 10);

        $currency = Currency::where('delete', false)->where(function($query) use($search){
            $query = $query->where('name', "LIKE", "%{$search}%")
            ->orWhere("shortcode", 'LIKE', "%{$search}%")
            ->orWhere("symbol", 'LIKE', "%{$search}%");
        });
        if ($paginated === 'no') {
            return response()->json($currency->with("country")->get(), 200);  
        }
        return response()->json($currency->with("country")->paginate(10), 200);  
    }
    public function create(){
    }
    public function store(Request $request){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255',
                'shortcode'=> 'required|string|min:2|max:4',
                'symbol' => 'required|string',
                'country_id' => 'required|integer|exists:countries,id|unique:currencies,country_id'
            ]);

            $currency = Currency::create($validatedData);

            if ($currency) {
                return response()->json(['message' => 'exito'], 201);
            }
            else{
                return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 500);
            }
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function show($id){
        return response()->json(Currency::with('country')->find($id), 200);
    }
    public function update(Request $request, $id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255',
                'shortcode'=> 'required|string|min:2|max:4',
                'symbol' => 'required|string'
            ]);
            $Currency = Currency::find($id);
            foreach ($validatedData as $field => $value) {
                $Currency->$field = $value;
            }
            $Currency->save();

            return response()->json(['message'=> 'exito'], 201);
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function destroy($id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $currency = Currency::find($id);
            $currency->country_id = null;
            $currency->delete = true;
            if ( $currency->save()) {
                return response()->json(['message' => 'exito'], 201);
            }
            else{
                return response()->json(['message' => 'error'], 404);
            }
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
}
