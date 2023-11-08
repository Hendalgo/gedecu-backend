<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request){
        $currentUser = User::find(auth()->user()->id);
        if ($currentUser->role->id === 1) {
            $order = $request->get('order', 'created_at');
            $orderBy = $request->get('order_by', 'desc');
            $role = $request->get('role');
            $since = $request->get('since');
            $until = $request->get('until');
            $search = $request->get('search');
            $per_page = $request->get('per_page', 10);
            $paginated = $request->get('paginated', 'yes');
            $users = User::query();
            if ($search) {
                $users = $users->join("countries", "users.country_id", "=", "countries.id")
                    ->select("users.*", "countries.name as country_name")
                    ->where('users.name', 'LIKE', "%{$search}%")
                    ->orWhere('users.email', 'LIKE', "%{$search}%")
                    ->orWhere('countries.name', 'LIKE', "%{$search}%");
            }
            if($role){
                $users = $users->where('role_id', "=", $role);
            }
            if ($order) {
                $users = $users->orderBy($order, $orderBy);
            }
            if ($paginated === 'no') { 
                return response()->json($users->where('delete',false)->with('role', 'country')->get(), 200);
            }
            return response()->json($users->where('delete',false)->with('role', 'country')->paginate($per_page), 200);
        }
        return response()->json(['message' => 'forbiden'], 401);
    }
    public function create(){
    }
    public function store(Request $request){
        $currentUser = User::find(auth()->user()->id);
        if ($currentUser->role->id === 1) {
            $messages = [
                'name.required' => 'El nombre es requerido',
                'email.unique' => 'Usuario ya registrado',
                'email.required' => 'Email requerido',
                'password.required' => 'Contraseña requerida',
                'password.confirmed' => 'Contraseña no coincide con la confirmación',
                'password.min' => 'Contraseña requiere minimo 8 caracteres',
                'password.max' => 'Contraseña máximo 16 caracteres',
                'country.exist' => 'País no registrado',
                'role.exist' => 'Rol inválido' 
            ];
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'email'=> 'required|email|unique:users',
                'password' => 'required|min:8|max:16|confirmed',
                'image'=> 'image',
                'country' => 'required|exists:countries,id',
                'role' => 'required|exists:roles,id'
            ], $messages);

            if (isset($validatedData['image'] )) {
                $imageName = time().'.'.$request->image->extension();  
                $request->image->move(public_path('images'), $imageName);
                $validatedData['img'] = asset('images/'.$imageName);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'country_id' => $request->country,
                'role_id'=> $request->role
            ]);
    
            if ($user) {
                return response()->json(['message' => 'exito'], 201);
            }
            else{
                return response()->json(['error'=> 'Hubo un problema al crear el usuario'], 500);
            }
        }
        return response()->json(['message' => 'forbiden'], 401);
    }
    public function show($id){
        $currentUser = User::find(auth()->user()->id);
        if ($currentUser->role->id === 1) {
            return response()->json(User::find($id), 200);
        }
        return response()->json(['message' => 'forbiden'], 401);
    }
    public function update(Request $request, $id){
        $currentUser = User::find(auth()->user()->id);
        if ($currentUser->role->id === 1) {
            $messages = [
                'name.required' => 'El nombre es requerido',
                'email.unique' => 'Usuario ya registrado',
                'email.required' => 'Email requerido',
                'password.required' => 'Contraseña requerida',
                'password.confirmed' => 'Contraseña no coincide con la confirmación',
                'password.min' => 'Contraseña requiere minimo 8 caracteres',
                'password.max' => 'Contraseña máximo 16 caracteres',
                'country_id.exist' => 'País no registrado',
                'role_id.exist' => 'Rol inválido' 
            ];
            $validatedData = $request->validate([
                'name'=> 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'password' => 'min:8|max:16|confirmed',
                'image'=> 'image',
                'country_id' => 'required|exists:countries,id',
                'role_id' => 'required|exists:roles,id'
            ], $messages);

            if (isset($validatedData['image'])) {
                $imageName = time().'.'.$request->image->extension();  
                $request->image->move(public_path('images'), $imageName);
                $validatedData['img'] = asset('images/'.$imageName);
            }

            $user = User::find($id);

            foreach ($validatedData as $field => $value) {
                $user->$field = $value;
                if ($field === 'email') {
                    $user->$field = $user->email;
                }
            }
            $user->save();
    
            return response()->json(['message'=> 'exito'], 201);
        }
        return response()->json(['message' => 'forbiden'], 401);

    }
    public function destroy($id){
        $currentUser = User::find(auth()->user()->id);
        if (auth()->user()->id == $id) {
            return response()->json(['message' => 'forbiden'], 401);
        }
        if ($currentUser->role->id === 1) {
            $user = User::find($id);
            $user->delete = true;
            if ($user->save()) {
                return response()->json(['message' => 'exito'], 200);
            }
            else{
                return response()->json(['message' => 'error'], 404);
            }
        }
        return response()->json(['message' => 'forbiden'], 401);
    }
    public function getUserRoles(){
        $roles = Role::withCount(['users as count'])->get();

        return response()->json($roles, 200);
    }
}
