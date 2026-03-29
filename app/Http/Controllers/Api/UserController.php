<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users, optionally filtered by role.
     */
    public function index(Request $request)
    {
        $request->validate([
            'role' => 'sometimes|in:admin,owner,customer',
        ]);

        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        return response()->json($query->paginate(10));
    }

    /**
     * Store a newly created user (Admin use case).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,customer,owner',
            'city' => 'nullable|string',
            'venue_name' => 'nullable|string',
            'venue_address' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'city' => $request->city,
            'venue_name' => $request->venue_name,
            'venue_address' => $request->venue_address,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'role' => 'sometimes|in:admin,customer,owner',
            'city' => 'sometimes|string|max:255',
            'venue_name' => 'sometimes|string|max:255',
            'venue_address' => 'sometimes|string',
        ]);

        $user->update($request->all());

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Update payment keys for an owner (Admin only).
     */
    public function updatePaymentKeys(Request $request, $id)
    {
        $user = User::where('role', 'owner')->findOrFail($id);

        $request->validate([
            'razorpay_key' => 'required|string',
            'razorpay_secret' => 'required|string',
        ]);

        $user->update([
            'payment_settings' => [
                'razorpay_key' => $request->razorpay_key,
                'razorpay_secret' => $request->razorpay_secret,
            ],
        ]);

        $user->makeVisible(['payment_settings']);

        return response()->json([
            'message' => 'Payment settings updated successfully',
            'user' => $user
        ]);
    }
}
