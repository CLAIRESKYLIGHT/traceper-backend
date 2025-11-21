<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // -------------------------------
    // REGISTER (Citizen default)
    // -------------------------------
    public function register(Request $request)
    {
        $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        // remove role from public validation
        ]);

    // Default role is citizen
        $role = 'citizen';

    // If an authenticated admin hits this endpoint and supplies role, allow it.
        if ($request->user() && $request->user()->role === 'admin' && $request->filled('role')) {
        // validate the role value
        if (in_array($request->role, ['admin','staff','citizen'])) {
            $role = $request->role;
        }
    }

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => $role,
    ]);

    $token = $user->createToken('api_token')->plainTextToken;

    return response()->json([
        'message' => 'User registered successfully',
        'token' => $token,
        'user' => $user,
    ], 201);
    }

    // -------------------------------
    // LOGIN
    // -------------------------------
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Incorrect email or password.'],
            ]);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    // -------------------------------
    // AUTHENTICATED USER
    // -------------------------------
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // -------------------------------
    // LOGOUT
    // -------------------------------
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
