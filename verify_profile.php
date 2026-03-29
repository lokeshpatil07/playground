<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

echo "--- VERIFYING UNIFIED PROFILE API ---\n";

// 1. Test Customer Profile
$customer = User::where('role', 'customer')->first();
if ($customer) {
    echo "\nTesting Customer Profile (ID: {$customer->id}):\n";
    $resource = new UserResource($customer);
    $data = $resource->toArray(new Request());
    print_r($data);
    
    if (isset($data['venue_name'])) {
        echo "ERROR: venue_name should not be present for customer.\n";
    } else {
        echo "SUCCESS: Customer profile logic correct.\n";
    }
}

// 2. Test Owner Profile
$owner = User::where('role', 'owner')->first();
if ($owner) {
    echo "\nTesting Owner Profile (ID: {$owner->id}):\n";
    $resource = new UserResource($owner);
    $data = $resource->toArray(new Request());
    print_r($data);
    
    if (!array_key_exists('venue_name', $data)) {
        echo "ERROR: venue_name should be present for owner.\n";
    } else {
        echo "SUCCESS: Owner profile logic correct.\n";
    }
}

echo "\n--- VERIFICATION COMPLETED ---\n";
