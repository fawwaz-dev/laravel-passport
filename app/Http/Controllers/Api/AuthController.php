<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $accessToken = $user->createToken('Personal Access Token')->accessToken;
        if (!$accessToken) {
            return response()->json(['message' => 'Failed to create access token'], 500);
        }


        return response()->json(
            ['message' => 'User registered successfully', 'accessToken' => $accessToken,  'user' => $user],
            201,
        );
    }

    public function login(Request $request)
    {
        $data = [
            'grant_type'    => 'password',
            'client_id'     => config('services.passport.client_id'),
            'client_secret' => config('services.passport.client_secret'),
            'username'      => $request->email,
            'password'      => $request->password,
            'scope'         => '',
        ];

        // 1. Buat PSR‑7 request internal
        $internal = Request::create('/oauth/token', 'POST', $data);
        // 2. Dispatch ke kernel Laravel
        $response = app()->handle($internal);

        return response()->json(json_decode($response->getContent(), true), $response->getStatusCode());
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
        if ($user) {
            $user->tokens()->delete();
            return response()->json(['message' => 'User logged out successfully'], 200);
        }
        return response()->json(['message' => 'User not found'], 404);
    }
}
