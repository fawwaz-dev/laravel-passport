<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $refreshToken = $user->createToken('Personal Access Token')->refreshToken;
        if (!$refreshToken) {
            return response()->json(['message' => 'Failed to create refresh token'], 500);
        }

        return response()->json(
            ['message' => 'User registered successfully', 'accessToken' => $accessToken, 'refreshToken' => $refreshToken, 'user' => $user],
            201,
        );
    }

    public function login()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', request('email'))->first();
        if (!$user || !Hash::check(request('password'), $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $token = $user->createToken('Personal Access Token')->accessToken;
        return response()->json(
            ['message' => 'User logged in successfully', 'token' => $token, 'user' => $user],
            200,
        );
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
