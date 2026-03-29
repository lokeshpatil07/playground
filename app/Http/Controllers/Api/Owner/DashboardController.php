<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Turf;
use App\Models\Setting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the owner's dashboard statistics.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        
        $totalTurfs = Turf::where('owner_id', $userId)->count();
        $totalBookings = Booking::whereHas('turf', function($query) use ($userId) {
            $query->where('owner_id', $userId);
        })->where('status', 'confirmed')->count();

        $totalReviews = Review::whereHas('turf', function($query) use ($userId) {
            $query->where('owner_id', $userId);
        })->count();

        $averageRating = Review::whereHas('turf', function($query) use ($userId) {
            $query->where('owner_id', $userId);
        })->avg('rating') ?: 0;

        // Calculate total revenue after admin cut
        $grossRevenue = Booking::whereHas('turf', function($query) use ($userId) {
            $query->where('owner_id', $userId);
        })->where('status', 'confirmed')->sum('total_price') ?: 0;

        $commissionRate = Setting::get('admin_commission', 10);
        $netRevenue = $grossRevenue * (1 - ($commissionRate / 100));

        return response()->json([
            'message' => 'Welcome to the Owner Dashboard',
            'stats' => [
                'total_turfs' => $totalTurfs,
                'total_bookings' => $totalBookings,
                'total_reviews' => $totalReviews,
                'average_rating' => round($averageRating, 1),
                'revenue' => round($netRevenue, 2),
                'balance' => round($request->user()->balance, 2)
            ]
        ]);
    }
}
