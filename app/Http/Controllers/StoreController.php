<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request){
        $order = $request->get('order');
        $orderBy = $request->get('order_by', 'desc');
        $since = $request->get('since');
        $until = $request->get('until');
        $search = $request->get('search');

        $store = Store::with('country')->with('user')
            ->when($search, function ($query, $search){
                $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('location', 'LIKE', "%{$search}%")
                ->orWhereHas('country', function ($query) use ($search){
                    $query->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('user', function ($query) use ($search){
                    $query->where('name', 'LIKE', "%{$search}%");
                });
            });

        return response()->json($store->paginate(10), 200);  
    }
    public function create(){
    }
    public function store(Request $request){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $messages = [
                'name.required' => 'El nombre es un campo requerido',
                'location.required' => 'Dirección requerida',
                'user.required' => 'Usuario requerido',
                'user.exist' => 'Usuario no existe',
                'country.required' => 'País requerido'
            ];
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'location'=> 'required|string|min:2|max:255',
                'user_id' => 'required|exists:users,id',
                'country_id' => 'required|exists:countries,id'
            ], $messages);

            $Store = Store::create($validatedData);
    
            if ($Store) {
                return response()->json(['message' => 'exito'], 201);
            }
            else{
                return response()->json(['error'=> 'Hubo un problema al crear el reporte'], 500);
            }
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function show($id){
        return response()->json(Store::find($id), 200);
    }
    public function update(Request $request, $id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'location'=> 'required|string|min:2|max:255',
                'user_id' => 'required|exists:users,id',
                'country_id' => 'required|exists:countries,id'
            ]);
            $Store = Store::find($id);
            foreach ($validatedData as $field => $value) {
                $Store->$field = $value;
            }
            $Store->save();
    
            return response()->json(['message'=> 'exito'], 200);
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
    public function destroy($id){
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $Store = Store::find($id);

            if ($Store) {
                $Store->delete();
                return response()->json(['message' => 'exito'], 200);
            }
            else{
                return response()->json(['message' => 'error'], 404);
            }
        }
        return response()->json(['message' => 'forbiden', 401]);
    }
}
