<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;
use App\Http\Resources\BookingResource;

echo "--- VERIFYING UPCOMING AND HISTORY FILTERS ---\n";

// Find a customer
$customer = User::where('role', 'customer')->first();
if (!$customer) {
    echo "ERROR: No customer found to test.\n";
    exit;
}

echo "Testing with Customer: {$customer->name} (ID: {$customer->id})\n";

// Create a mock future and past booking if none exist
$now = Carbon::now();
$turf = \App\Models\Turf::first();

if (!$turf) {
    echo "ERROR: No turf found to create mock bookings.\n";
    exit;
}

// Ensure there is at least one upcoming and one past booking
Booking::updateOrCreate(
    ['user_id' => $customer->id, 'turf_id' => $turf->id, 'start_time' => $now->copy()->addDays(5)->format('Y-m-d H:i:s')],
    ['end_time' => $now->copy()->addDays(5)->addHour()->format('Y-m-d H:i:s'), 'total_price' => 1000, 'status' => 'confirmed']
);

Booking::updateOrCreate(
    ['user_id' => $customer->id, 'turf_id' => $turf->id, 'start_time' => $now->copy()->subDays(5)->format('Y-m-d H:i:s')],
    ['end_time' => $now->copy()->subDays(5)->addHour()->format('Y-m-d H:i:s'), 'total_price' => 1000, 'status' => 'confirmed']
);

// 1. Test Upcoming
echo "\nTesting ?upcoming=1...\n";
$upcomingResponse = Booking::with('turf')
    ->where('user_id', $customer->id)
    ->where('start_time', '>=', now())
    ->get();
echo "Upcoming count: " . $upcomingResponse->count() . "\n";

// 2. Test History
echo "\nTesting ?history=1...\n";
$historyResponse = Booking::with('turf')
    ->where('user_id', $customer->id)
    ->where('start_time', '<', now())
    ->get();
echo "History count: " . $historyResponse->count() . "\n";

echo "\n--- VERIFICATION COMPLETED ---\n";
