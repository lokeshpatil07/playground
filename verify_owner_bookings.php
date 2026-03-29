<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Booking;

echo "--- VERIFYING OWNER BOOKING API ---\n";

// Find an owner
$owner = User::where('role', 'owner')->first();
if (!$owner) {
    echo "ERROR: No owner found to test.\n";
    exit;
}

echo "Testing with Owner: {$owner->name} (ID: {$owner->id})\n";

// Get bookings for this owner's turfs
$bookings = $owner->ownerBookings()->with(['turf', 'user'])->get();

echo "Total Bookings found for this owner: " . $bookings->count() . "\n";

if ($bookings->count() > 0) {
    echo "\nSample Booking Details:\n";
    foreach ($bookings as $b) {
        echo "- Booking ID: {$b->id}\n";
        echo "  Venue: {$b->turf->name}\n";
        echo "  Customer: {$b->user->name}\n";
        echo "  Amount: ₹{$b->total_price}\n";
        echo "  Status: {$b->status}\n";
    }
} else {
    echo "No bookings yet for this owner. (This is expected if they haven't received any bookings).\n";
}

echo "\n--- VERIFICATION COMPLETED ---\n";
