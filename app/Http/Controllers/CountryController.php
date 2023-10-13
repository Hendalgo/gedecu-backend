<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index(){
        return response()->json(Country::paginate(10), 200);  
    }
    public function create(){
    }
    public function store(Request $request){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'shortcode'=> 'required|string|min:2|max:4',
                'image'=> 'image',
                'currency_id' => 'required|exists:currencies,id'
            ]);

            if ($validatedData['image']) {
                $imageName = time().'.'.$request->image->extension();  
                $request->image->move(public_path('images'), $imageName);
                $validatedData['img'] = asset('images/'.$imageName);
            }

            $country = Country::create($validatedData);
    
            if ($country) {
                return response()->json(['message' => 'exito'], 200);
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
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'shortcode'=> 'required|string|min:2|max:4',
                'image'=> 'image',
                'currency_id' => 'exists:currencies,id'
            ]);
            
            if ($validatedData['image']) {
                $imageName = time().'.'.$request->image->extension();  
                $request->image->move(public_path('images'), $imageName);
                $validatedData['img'] = asset('images/'.$imageName);
            }
            $country = Country::find($id);
            foreach ($validatedData as $field => $value) {
                $country->$field = $value;
            }
            $country->save();
    
            return response()->json(['message'=> 'exito'], 200);
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function destroy($id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $country = Country::find($id);

            if ($country) {
                $country->delete();
                return response()->json(['message' => 'exito'], 200);
            }
            else{
                return response()->json(['message' => 'error'], 404);
            }
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function getBanksCount(){
        return response()->json(Country::withCount(['banks as count'])->get(), 200);
    }
}
