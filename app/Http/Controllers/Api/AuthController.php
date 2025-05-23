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
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

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

        return response()->json(
            ['message' => 'User registered successfully',  'user' => $user],
            201,
        );
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|exists:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $data = [
            'grant_type'    => $request->grant_type,
            'client_id'     => config('services.passport.client_id'),
            'client_secret' => config('services.passport.client_secret'),
            'username'      => $request->email,
            'password'      => $request->password,
            'scope'         => '',
        ];

        // Buat PSR-7 request internal
        $symfony = SymfonyRequest::create(
            '/oauth/token',
            'POST',
            $data,
            [],
            [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded']
        );
        $req = Request::createFromBase($symfony);
        app()->instance('request', $req);

        // Dispatch tanpa HTTP eksternal
        $res = app()->handle($req);

        return response()->json(json_decode($res->getContent(), true), $res->getStatusCode());
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

    public function refresh(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'grant_type'    => 'required|string',
            'refresh_token' => 'required|string',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        $data = [
            'grant_type'    => $request->grant_type,
            'client_id'     => config('services.passport.client_id'),
            'client_secret' => config('services.passport.client_secret'),
            'refresh_token' => $request->refresh_token,
            'scope'         => '',
        ];

        // Buat PSR-7 request internal
        $symfony = SymfonyRequest::create(
            '/oauth/token',
            'POST',
            $data,
            [],
            [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded']
        );
        $req = Request::createFromBase($symfony);
        app()->instance('request', $req);

        // Dispatch tanpa HTTP eksternal
        $res = app()->handle($req);

        return response()->json(json_decode($res->getContent(), true), $res->getStatusCode());
    }
}
