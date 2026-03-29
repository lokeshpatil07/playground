<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Turf;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Razorpay\Api\Api;
use App\Http\Resources\BookingResource;

class BookingController extends Controller
{
    private function getRazorpayKey()
    {
        return Setting::get('razorpay_key', env('RAZORPAY_KEY'));
    }

    private function getRazorpayApi()
    {
        $key = Setting::get('razorpay_key', env('RAZORPAY_KEY'));
        $secret = Setting::get('razorpay_secret', env('RAZORPAY_SECRET'));
        
        return new Api($key, $secret);
    }

    /**
     * List bookings for the authenticated customer.
     */
    public function index(Request $request)
    {
        $query = Booking::with('turf')
            ->where('user_id', $request->user()->id);

        if ($request->boolean('upcoming')) {
            $query->where('start_time', '>=', now());
        } elseif ($request->boolean('history')) {
            $query->where('start_time', '<', now());
        }

        $bookings = $query->orderBy('start_time', 'desc')
            ->paginate(15);
            
        return BookingResource::collection($bookings);
    }

    /**
     * Create a new booking.
     */
    public function store(Request $request)
    {
        $request->validate([
            'turf_id' => 'required|exists:turfs,id',
            'slot_id' => 'required|exists:turf_slots,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $slot = \App\Models\TurfSlot::where('turf_id', $request->turf_id)->findOrFail($request->slot_id);
        
        // Simple conflict check for the specific date and slot
        $exists = Booking::where('turf_id', $request->turf_id)
            ->where('start_time', $request->date . ' ' . $slot->start_time)
            ->where('status', 'confirmed')
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'This slot is already booked for the selected date'], 422);
        }

        $totalPrice = $slot->price;

        // Initialize Razorpay
        $api = $this->getRazorpayApi();

        // Create Razorpay Order
        $orderData = [
            'receipt'         => 'rcpt_' . time(),
            'amount'          => $totalPrice * 100, // in paise
            'currency'        => 'INR',
        ];

        try {
            $razorpayOrder = $api->order->create($orderData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Razorpay Order Creation Failed: ' . $e->getMessage()], 500);
        }

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'turf_id' => $request->turf_id,
            'start_time' => $request->date . ' ' . $slot->start_time,
            'end_time' => $request->date . ' ' . $slot->end_time,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'payment_status' => 'pending',
            'razorpay_order_id' => $razorpayOrder['id'],
        ]);

        return response()->json([
            'message' => 'Booking initiated. Please complete payment.',
            'booking' => $booking,
            'razorpay_order_id' => $razorpayOrder['id'],
            'razorpay_key' => $this->getRazorpayKey(),
            'amount' => $totalPrice * 100,
            'currency' => 'INR',
            'venue_name' => $slot->turf->name,
        ], 201);
    }

    /**
     * Verify Razorpay Payment.
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required',
        ]);

        $api = $this->getRazorpayApi();

        try {
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];

            $api->utility->verifyPaymentSignature($attributes);
        } catch(\Exception $e) {
            return response()->json(['message' => 'Payment verification failed: ' . $e->getMessage()], 422);
        }

        $booking = Booking::with('turf.owner')->where('razorpay_order_id', $request->razorpay_order_id)->firstOrFail();
        
        if ($booking->payment_status === 'paid') {
            return response()->json(['message' => 'Payment already verified', 'booking' => $booking]);
        }

        // Update booking
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ]);

        // Credit Owner Wallet & Send Notifications
        $owner = $booking->turf->owner ?? null;
        if ($owner) {
            $commissionRate = Setting::get('admin_commission', 10);
            $ownerShare = $booking->total_price * (1 - ($commissionRate / 100));
            $owner->increment('balance', $ownerShare);
            
            // Notify Owner
            try {
                $owner->notify(new \App\Notifications\BookingConfirmed($booking));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to notify owner: ' . $e->getMessage());
            }
        }

        // Notify Customer
        try {
            $booking->user->notify(new \App\Notifications\BookingConfirmed($booking));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to notify customer: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Payment verified and booking confirmed successfully',
            'booking' => $booking
        ]);
    }

    /**
     * Display the specified booking for the customer.
     */
    public function show($id, Request $request)
    {
        $booking = Booking::with('turf')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);
            
        return new BookingResource($booking);
    }

    /**
     * Cancel a booking.
     */
    public function cancel($id, Request $request)
    {
        $booking = Booking::where('user_id', $request->user()->id)->findOrFail($id);
        
        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled'], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Booking cancelled successfully']);
    }
}
