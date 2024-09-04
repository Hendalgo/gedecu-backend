<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {

            $search = $request->get('search');
            $countries = Country::query();
            if ($search) {
                $countries = $countries->where(function ($query) use ($search) {
                    $query->where('countries.name', 'LIKE', '%'.$search.'%')
                        ->orWhere('countries.shortcode', 'LIKE', '%'.$search.'%');
                });
            }

            $countries = $countries->where('countries.delete', false)->with('banks')->paginate(10);

            return response()->json($countries, 200);
        }

        return response()->json(['message' => 'forbiden', 403]);
    }

    public function store(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $message = [
                'country_name.required' => 'El nombre del país es requerido',
                'country_shortcode.required' => 'El código de país es requerido',
                'country_name.regex' => 'El nombre del país solo puede contener letras',
                'country_shortcode.regex' => 'El código de país solo puede contener letras',
            ];
            $validatedData = $request->validate([
                'country_name' => "required|string|max:255|regex:/^[a-zA-Z\s]+$/",
                'country_shortcode' => 'required|string|min:2|max:4|regex:/^[a-zA-Z\s]+$/',
                'locale' => 'string|max:20',
            ], $message);
            $country = Country::create([
                'name' => $validatedData['country_name'],
                'shortcode' => $validatedData['country_shortcode'],
                'config' => json_encode([]),
                'locale' => $validatedData['locale'],
            ]);

            if ($country) {
                return response()->json(['message' => 'exito'], 201);
            } else {
                return response()->json(['error' => 'Hubo un problema al crear el reporte'], 500);
            }

        }

        return response()->json(['message' => 'forbiden', 403]);
    }

    public function show($id)
    {
        return response()->json(Country::find($id), 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $message = [
                'country_name.required' => 'El nombre del país es requerido',
                'country_shortcode.required' => 'El código de país es requerido',
                'country_name.regex' => 'El nombre del país solo puede contener letras',
                'country_shortcode.regex' => 'El código de país solo puede contener letras',
            ];
            $validatedData = $request->validate([
                'country_name' => "required|string|max:255|regex:/^[a-zA-Z\s]+$/",
                'country_shortcode' => 'required|string|min:2|max:4|regex:/^[a-zA-Z\s]+$/',
                'locale' => 'string|max:20',
            ], $message);

            $country = Country::find($id);
            $country->name = $validatedData['country_name'];
            $country->shortcode = $validatedData['country_shortcode'];
            if ($validatedData['locale']) {
                $country->locale = $validatedData['locale'];
            }
            if ($country->save()) {
                return response()->json(['message' => 'exito'], 201);
            } else {
                return response()->json(['error' => 'Hubo un problema al crear el reporte'], 500);
            }
        }

        return response()->json(['message' => 'forbiden', 403]);
    }

    public function destroy($id)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $country = Country::find($id);
            if ($country->is_initial) {
                return response()->json(['error' => 'No puedes eliminar el país inicial'], 403);
            }
            $country->delete();

            return response()->json(['message' => 'exito'], 201);
        }

        return response()->json(['message' => 'forbiden', 403]);
    }

    public function getBanksCount()
    {
        return response()->json(Country::where('delete', false)->withCount(['banks as count'])->get(), 200);
    }
}
