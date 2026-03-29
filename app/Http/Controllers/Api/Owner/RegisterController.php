<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    /**
     * Register a new owner.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'city' => 'required|string|max:255',
            'venue_name' => 'required|string|max:255',
            'venue_address' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'owner',
            'city' => $request->city,
            'venue_name' => $request->venue_name,
            'venue_address' => $request->venue_address,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Owner registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    /**
     * Upgrade an existing customer to owner.
     */
    public function upgrade(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'owner') {
            return response()->json(['message' => 'User is already an owner'], 422);
        }

        $request->validate([
            'city' => 'required|string|max:255',
            'venue_name' => 'required|string|max:255',
            'venue_address' => 'required|string',
        ]);

        $user->update([
            'role' => 'owner',
            'city' => $request->city,
            'venue_name' => $request->venue_name,
            'venue_address' => $request->venue_address,
        ]);

        return response()->json([
            'message' => 'User upgraded to owner successfully',
            'user' => $user
        ]);
    }
}
