<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings for the owner's turfs.
     */
    public function index(Request $request)
    {
        $bookings = $request->user()->ownerBookings()
            ->with(['turf', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return response()->json($bookings);
    }

    /**
     * Display the specified booking.
     */
    public function show($id, Request $request)
    {
        $booking = $request->user()->ownerBookings()
            ->with(['turf', 'user'])
            ->findOrFail($id);
            
        return response()->json($booking);
    }
}
