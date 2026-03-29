<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = App\Models\Booking::where('razorpay_order_id', 'order_SX0BZY8lmLW7Mh')->first();
if (!$booking) {
    echo "ERROR: Booking not found for order_SX0BZY8lmLW7Mh\n";
    exit;
}

echo "Booking ID: " . $booking->id . "\n";
echo "Turf ID: " . $booking->turf_id . "\n";
$turf = $booking->turf;
if (!$turf) {
    echo "ERROR: Turf not found for ID " . $booking->turf_id . "\n";
} else {
    echo "Turf Name: " . $turf->name . "\n";
    echo "Owner ID: " . $turf->owner_id . "\n";
    $owner = $turf->owner;
    if (!$owner) {
        echo "ERROR: Owner not found for ID " . $turf->owner_id . "\n";
    } else {
        echo "Owner Name: " . $owner->name . "\n";
    }
}
