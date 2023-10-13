<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(){
        return response()->json(Currency::paginate(10), 200);  
    }
    public function create(){
    }
    public function store(Request $request){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'shortcode'=> 'required|string|min:2|max:4',
                'symbol' => 'required|string'
            ]);

            $Currency = Currency::create($validatedData);

            if ($Currency) {
                return response()->json(['message' => 'exito'], 200);
            }
            else{
                return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 500);
            }
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function show($id){
        return response()->json(Currency::find($id), 200);
    }
    public function update(Request $request, $id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'shortcode'=> 'required|string|min:2|max:4',
                'symbol' => 'required|string'
            ]);
            $Currency = Currency::find($id);
            foreach ($validatedData as $field => $value) {
                $Currency->$field = $value;
            }
            $Currency->save();

            return response()->json(['message'=> 'exito'], 200);
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function destroy($id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $Currency = Currency::find($id);

            if ($Currency) {
                $Currency->delete();
                return response()->json(['message' => 'exito'], 200);
            }
            else{
                return response()->json(['message' => 'error'], 404);
            }
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
}
