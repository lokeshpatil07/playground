<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Turf;
use App\Models\User;
use App\Models\PayoutRequest;
use App\Models\Setting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics.
     */
    public function index()
    {
        $totalRevenue = Booking::where('status', 'confirmed')->sum('total_price') ?: 0;
        
        $commissionRate = Setting::get('admin_commission', 10);
        $adminRevenue = $totalRevenue * ($commissionRate / 100);

        return response()->json([
            'stats' => [
                'total_users' => User::count(),
                'total_owners' => User::where('role', 'owner')->count(),
                'total_customers' => User::where('role', 'customer')->count(),
                'total_turfs' => Turf::count(),
                'total_bookings' => Booking::count(),
                'pending_payouts' => PayoutRequest::where('status', 'pending')->count(),
                'total_revenue' => round($totalRevenue, 2),
                'admin_revenue' => round($adminRevenue, 2),
                'commission_rate' => $commissionRate,
                'razorpay_key' => Setting::get('razorpay_key', env('RAZORPAY_KEY')),
            ]
        ]);
    }
}
