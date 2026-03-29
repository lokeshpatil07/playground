<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    /**
     * List all payout requests for this owner.
     */
    public function index(Request $request)
    {
        $payouts = PayoutRequest::where('owner_id', $request->user()->id)
                                ->latest()
                                ->paginate(10);
        
        return response()->json($payouts);
    }

    /**
     * Request a new payout.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $owner = $request->user();

        if ($owner->balance < $request->amount) {
            return response()->json([
                'message' => 'Insufficient balance for payout request. Your current balance is ' . number_format($owner->balance, 2)
            ], 422);
        }

        // Optional: Also check for existing pending requests?
        $hasPending = PayoutRequest::where('owner_id', $owner->id)->where('status', 'pending')->exists();
        if ($hasPending) {
            return response()->json(['message' => 'You already have a pending payout request'], 422);
        }
        
        $payout = PayoutRequest::create([
            'owner_id' => $owner->id,
            'amount' => $request->amount,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Payout requested successfully',
            'payout' => $payout
        ], 201);
    }
}
