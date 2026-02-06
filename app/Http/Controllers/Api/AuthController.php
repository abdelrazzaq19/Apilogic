<?php

namespace App\Http\Controllers\Api;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            // 'role' => 'nullable|in:admin,attendee',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // 'role' => $request->role ?? 'attendee',
        ]);

        $data = [
            'token' => $user->createToken('api_token')->plainTextToken,
            'user' => $user,
        ];

        return $this->successResponse($data, 'User registered successfully', 201);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->errorResponse('User with this email does not exist', 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $data = [
            'token' => $user->createToken('api_token')->plainTextToken,
            'user' => $user,
        ];

        return $this->successResponse($data, 'User logged in successfully', 200);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'User logged out successfully', 200);
    }
}
