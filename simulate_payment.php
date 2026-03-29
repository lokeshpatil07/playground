<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Turf;
use App\Models\TurfSlot;
use App\Models\Booking;
use App\Models\Setting;
use Razorpay\Api\Api;

echo "--- PAYMENT SIMULATION STARTED ---\n";

// 1. Get sample data
$customer = User::where('role', 'customer')->first();
$owner = User::where('role', 'owner')->first();
$turf = Turf::first();
$slot = TurfSlot::where('turf_id', $turf->id)->first();

if (!$customer || !$owner || !$turf || !$slot) {
    echo "ERROR: Data missing for simulation.\n";
    exit(1);
}

echo "Customer: {$customer->name} (ID: {$customer->id})\n";
echo "Venue: {$turf->name} (Owner: {$owner->name})\n";
echo "Slot: {$slot->start_time} - {$slot->end_time} (Price: {$slot->price})\n";

// 2. Simulate Creating a Booking (store method logic)
echo "\n[1] Creating Booking record...\n";
$booking = Booking::create([
    'user_id' => $customer->id,
    'turf_id' => $turf->id,
    'start_time' => date('Y-m-d') . ' ' . $slot->start_time,
    'end_time' => date('Y-m-d') . ' ' . $slot->end_time,
    'total_price' => $slot->price,
    'status' => 'pending',
    'payment_status' => 'pending',
    'razorpay_order_id' => 'order_sim_' . uniqid(),
]);

echo "Booking Created! (ID: {$booking->id}, Status: {$booking->status})\n";

// 3. Simulate Payment Verification (verifyPayment method logic)
echo "\n[2] Simulating Payment Verification...\n";
echo "(Assuming user paid successfully on the frontend)\n";

$payment_id = 'pay_sim_' . uniqid();
$signature = 'sig_sim_' . uniqid();

// Update booking status
$booking->update([
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'razorpay_payment_id' => $payment_id,
    'razorpay_signature' => $signature,
]);

echo "Booking Status Updated: {$booking->status}, Payment Status: {$booking->payment_status}\n";

// 4. Simulate Payout Logic
echo "\n[3] Crediting Owner Wallet...\n";
$commissionRate = Setting::get('admin_commission', 10);
$ownerShare = $booking->total_price * (1 - ($commissionRate / 100));

$oldBalance = $owner->balance;
$owner->increment('balance', $ownerShare);
$newBalance = $owner->fresh()->balance;

echo "Admin Commission: {$commissionRate}%\n";
echo "Owner Share: {$ownerShare}\n";
echo "Owner Balance: {$oldBalance} -> {$newBalance}\n";

echo "\n--- SIMULATION COMPLETED SUCCESSFULLY ---\n";
echo "Note: This simulation bypassed the actual Razorpay API network call to demonstrate the system's logic flow.\n";
