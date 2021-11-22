<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        if ($validator->fails()) {

            $data = [
                'message' => $validator->errors(),
                'success' => false
            ];

            return response()->json($data, 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $payloads = [
            "message" => 'User has been successfully registered',
            "success" => true,
            'user' => $user
        ];

        return response()->json($payloads, 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {

            $data = [
                'message' => $validator->errors()
            ];

            return response()->json($data, 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Login failed',
                'success' => false
            ], 401);
        }

        $token = $user->createToken('user_token')->plainTextToken;

        $payloads = [
            'message' => 'Login successfull',
            'success' => true,
            'user' => $user,
            'token' => $token
        ];

        return response()->json($payloads);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response([
            "message" => 'Logged out',
            "success" => true
        ]);
    }
}
