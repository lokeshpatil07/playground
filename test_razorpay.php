<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Razorpay\Api\Api;
use App\Models\Setting;

echo "--- REAL RAZORPAY API TEST STARTED ---\n";

// Fetch keys from settings or env
$key = Setting::get('razorpay_key', env('RAZORPAY_KEY'));
$secret = Setting::get('razorpay_secret', env('RAZORPAY_SECRET'));
$mode = Setting::get('razorpay_mode', 'test');

echo "Testing in mode: {$mode}\n";
echo "Active Key: {$key}\n";

$api = new Api($key, $secret);

// Attempt to create a test order
$orderData = [
    'receipt'         => 'test_rcpt_' . time(),
    'amount'          => 100, // 1 INR in paise
    'currency'        => 'INR',
];

echo "\nCalling Razorpay API to create a test order...\n";

try {
    $order = $api->order->create($orderData);
    echo "SUCCESS: Order created successfully!\n";
    echo "Razorpay Order ID: " . $order['id'] . "\n";
    echo "Environment check: The keys are valid and working.\n";
} catch (\Exception $e) {
    echo "FAILED: Connection or Authentication error.\n";
    echo "Error Message: " . $e->getMessage() . "\n";
}

echo "\n--- TEST COMPLETED ---\n";
