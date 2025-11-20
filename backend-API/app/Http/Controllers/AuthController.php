<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request){
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if(Auth::attempt($request->only('email', 'password'))){
            $user = Auth::user();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login succesful',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 200);
        }

        return response()->json([
            'message' => 'Login Failed',
        ], 401);

    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'logged out']);
    }

    public function user(Request $request){
        return response()->json($request->user(), 200);
    }
}
