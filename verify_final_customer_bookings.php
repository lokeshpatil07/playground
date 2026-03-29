<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Http\Resources\BookingResource;

echo "--- FINAL VERIFICATION: CUSTOMER BOOKING API ---\n";

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

echo "Testing single booking resource output for ID: {$booking->id}...\n";

// Fetch with relationships
$result = Booking::with('turf')
    ->where('user_id', $customer->id)
    ->find($booking->id);

if ($result) {
    $resource = new BookingResource($result);
    $data = $resource->resolve();
    
    echo "SUCCESS: Data transformed!\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "FAILED: Booking not found.\n";
}

echo "\n--- VERIFICATION COMPLETED ---\n";
