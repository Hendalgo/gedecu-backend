<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $search = $request->get('search');
        $paginated = $request->get('paginated', 'yes');
        $per_page = $request->get('per_page', 10);

        if ($user->role->id === 1) {
            $currency = Currency::where('currencies.delete', false)->where(function ($query) use ($search) {
                $query = $query->where('currencies.name', 'LIKE', "%{$search}%")
                    ->orWhere('currencies.shortcode', 'LIKE', "%{$search}%")
                    ->orWhere('currencies.symbol', 'LIKE', "%{$search}%");
            });
            if ($paginated === 'no') {
                return response()->json($currency->get(), 200);
            }

            return response()->json($currency->paginate($per_page), 200);
        }
        
        //Check if the user has permissions to see the currencies
        $permissions = json_decode($user->permissions, true);
        if (isset($permissions['allowed_currencies'])) {
            $allowed_currencies = $permissions['allowed_currencies'];
        } else {
            $allowed_currencies = [];
        }
        $currency = Currency::where('currencies.delete', false)->where(function ($query) use ($search) {
            $query = $query->where('currencies.name', 'LIKE', "%{$search}%")
                ->orWhere('currencies.shortcode', 'LIKE', "%{$search}%")
                ->orWhere('currencies.symbol', 'LIKE', "%{$search}%");
        });

        if (count($allowed_currencies) > 0) {
            $currency = $currency->whereIn('currencies.id', $allowed_currencies);
        }

        if ($paginated === 'no') {
            return response()->json($currency->get(), 200);
        }

        return response()->json($currency->paginate(10), 200);
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
                'shortcode' => 'required|string|min:2|max:4|regex:/^[a-zA-Z\s]+$/',
                'symbol' => 'required|string',
            ]);

            $currency = Currency::create($validatedData);

            if ($currency) {
                return response()->json(['message' => 'exito'], 201);
            } else {
                return response()->json(['error' => 'Hubo un problema al crear el reporte'], 500);
            }
        }

        return response()->json(['message' => 'forbiden', 401]);
    }

    public function show(Request $request, $id)
    {
        $currency = Currency::query();
        $user = User::find(auth()->user()->id);
        $stores = $request->get('stores', 'no');

        if ($user->role->id === 1 && $stores === 'yes') {
            $currency = Store::rightjoin('banks_accounts', 'stores.id', '=', 'banks_accounts.store_id')
                ->where('banks_accounts.currency_id', $id)
                ->where('stores.delete', false)
                ->where('banks_accounts.account_type_id', 3)
                ->select('stores.*');

            return response()->json($currency->get(), 200);
        }

        $allowed_currencies = json_decode($user->permissions, true)['allowed_currencies'];
        if (!in_array($id, $allowed_currencies)) {
            return response()->json(['message' => 'forbiden'], 403);
        }

        return response()->json(Currency::find($id), 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'shortcode' => 'required|string|min:2|max:4',
                'symbol' => 'required|string',
            ]);
            $Currency = Currency::find($id);
            foreach ($validatedData as $field => $value) {
                $Currency->$field = $value;
            }
            $Currency->save();

            return response()->json(['message' => 'exito'], 201);
        }

        return response()->json(['message' => 'forbiden', 403]);
    }

    public function destroy($id)
    {
        $user = User::find(auth()->user()->id);
        if ($user->role->id === 1) {
            $currency = Currency::find($id);
            if ($currency->is_initial) {
                return response()->json(['message' => 'No se puede eliminar la moneda inicial'], 403);
            }
            $currency->delete();

            return response()->json(['message' => 'exito'], 201);
        }

        return response()->json(['message' => 'forbiden', 403]);
    }
}
