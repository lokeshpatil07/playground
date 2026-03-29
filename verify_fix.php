<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use Illuminate\Support\Facades\Notification;

echo "--- VERIFYING BUG FIX & NOTIFICATIONS ---\n";

// Find a pending booking
$booking = Booking::where('status', 'pending')->first();
if (!$booking) {
    echo "ERROR: No pending booking found to test.\n";
    exit;
}

echo "Testing with Booking ID: {$booking->id}\n";
echo "Venue: {$booking->turf->name}\n";
echo "Customer: {$booking->user->name}\n";

// Mimic the verifyPayment logic
echo "\nSimulating verifyPayment...\n";

// Eager load
$booking->load(['turf.owner', 'user']);
$owner = $booking->turf->owner;

if ($owner) {
    echo "Owner found: {$owner->name}. Proceeding to increment balance.\n";
} else {
    echo "ERROR: Owner is NULL! The bug is still present.\n";
    exit;
}

try {
    // Send notifications (will be logged to storage/logs/laravel.log)
    $owner->notify(new \App\Notifications\BookingConfirmed($booking));
    $booking->user->notify(new \App\Notifications\BookingConfirmed($booking));
    echo "SUCCESS: Notifications triggered successfully.\n";
} catch (\Exception $e) {
    echo "FAILED: Notification error - " . $e->getMessage() . "\n";
}

echo "\n--- VERIFICATION COMPLETED ---\n";
echo "Check storage/logs/laravel.log to see the 'sent' emails.\n";
