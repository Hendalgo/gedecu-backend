<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Faltan campos requeridos'], 422);
        }

        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (auth()->user()->delete) {
            return response()->json([
                'error' => 'El usuario ya no existe',
            ], 401);
        }

        return $this->responseWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user();
        $user->load('country', 'balance.currency', 'store', 'role', 'workingDays');

        // Verifica si el usuario tiene el rol de Encargado de Local (id 3)
        if ($user->role_id === 3) {
            // Busca la primera cuenta bancaria con type_id 1
            $bankAccount = BankAccount::where('account_type_id', 3)->where('store_id', $user->store->id)->first();

            if ($bankAccount) {
                // Carga la información de la moneda asociada a la cuenta bancaria
                $bankAccount->load('currency');
                $currencyInfo = $bankAccount->currency;
            } else {
                $currencyInfo = null;
            }

           $user->currency = $currencyInfo;
        }

        return response()->json($user);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        if (is_numeric(auth()->user()->id)) {
            auth()->logout();

            return response()->json(['message' => 'Successfully logged out']);
        } else {
            return response()->json(['message' => 'No user to logout'], 401);
        }
    }

    protected function responseWithToken($token)
    {
        $user = auth()->user();
        $user->load('country', 'balance.currency', 'store');
        $user->load('role');

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expiration' => auth()->factory()->getTTl(),
            'user' => $user,
        ]);
    }

    public function refreshToken()
    {

        $token = JWTAuth::getToken();

        if (! $token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            $newToken = JWTAuth::refresh($token);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token invalid'], 401);
        }

        return $this->responseWithToken($newToken);
    }
}
