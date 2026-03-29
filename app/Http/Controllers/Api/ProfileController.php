<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'city' => 'sometimes|string|max:255',
        ];

        // Owner specific fields
        if ($user->role === 'owner') {
            $rules['venue_name'] = 'sometimes|string|max:255';
            $rules['venue_address'] = 'sometimes|string';
        }

        $validated = $request->validate($rules);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user->fresh())
        ]);
    }
}
