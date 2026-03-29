<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Booking;

echo "--- VERIFYING CUSTOMER BOOKING API ---\n";

// Find a customer
$customer = User::where('role', 'customer')->first();
if (!$customer) {
    echo "ERROR: No customer found to test.\n";
    exit;
}

echo "Testing with Customer: {$customer->name} (ID: {$customer->id})\n";

// Get a booking for this customer
$booking = Booking::where('user_id', $customer->id)->first();

if (!$booking) {
    echo "No bookings yet for this customer.\n";
    exit;
}

echo "Testing GET /api/customer/bookings/{$booking->id} logic...\n";

// Fetch with relationships
$result = Booking::with('turf')
    ->where('user_id', $customer->id)
    ->find($booking->id);

if ($result) {
    echo "SUCCESS: Booking found!\n";
    echo "Booking ID: {$result->id}\n";
    echo "Venue: {$result->turf->name}\n";
    echo "Date: {$result->start_time}\n";
    echo "Status: {$result->status}\n";
} else {
    echo "FAILED: Booking not found or does not belong to the user.\n";
}

echo "\n--- VERIFICATION COMPLETED ---\n";
