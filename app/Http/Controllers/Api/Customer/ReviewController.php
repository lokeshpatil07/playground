<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Turf;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a new review for a turf.
     */
    public function store(Request $request)
    {
        $request->validate([
            'turf_id' => 'required|exists:turfs,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Optional: Check if user has booked this turf before?
        // For now, allow any authenticated user to review once.
        $existing = Review::where('user_id', $request->user()->id)
                          ->where('turf_id', $request->turf_id)
                          ->first();

        if ($existing) {
            return response()->json(['message' => 'You have already reviewed this turf'], 422);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'turf_id' => $request->turf_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review
        ], 201);
    }

    /**
     * Get all reviews for a specific turf.
     */
    public function index($turfId)
    {
        $reviews = Review::with('user:id,name')
                         ->where('turf_id', $turfId)
                         ->latest()
                         ->paginate(10);

        return response()->json($reviews);
    }
}
