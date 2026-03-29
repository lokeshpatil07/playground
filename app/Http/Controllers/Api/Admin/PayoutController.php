<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    /**
     * List all payout requests.
     */
    public function index(Request $request)
    {
        $query = PayoutRequest::with('owner:id,name,email,venue_name');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate(10));
    }

    /**
     * Approve or reject a payout request.
     */
    public function update(Request $request, $id)
    {
        $payout = PayoutRequest::findOrFail($id);

        $request->validate([
            'status' => 'required|in:approved,rejected,declined',
            'admin_notes' => 'nullable|string',
        ]);

        if ($request->status === 'approved' && $payout->status !== 'approved') {
            $owner = $payout->owner;
            
            if ($owner->balance < $payout->amount) {
                return response()->json(['message' => 'Owner does not have enough balance for this payout'], 422);
            }

            $owner->decrement('balance', $payout->amount);
        }

        $payout->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'processed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Payout status updated',
            'payout' => $payout
        ]);
    }
}
