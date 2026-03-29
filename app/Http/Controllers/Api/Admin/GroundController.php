<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Turf;
use Illuminate\Http\Request;

class GroundController extends Controller
{
    /**
     * List all grounds for admin.
     */
    public function index()
    {
        return response()->json(Turf::with(['owner:id,name,email', 'slots'])->paginate(10));
    }

    /**
     * Update any ground (admin can moderate).
     */
    public function update(Request $request, $id)
    {
        $turf = Turf::findOrFail($id);
        
        $request->validate([
            'status' => 'sometimes|in:active,inactive',
            'name' => 'sometimes|string',
            // ... add more as needed
        ]);

        $turf->update($request->all());

        return response()->json([
            'message' => 'Turf updated by admin',
            'turf' => $turf
        ]);
    }
}
