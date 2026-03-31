<?php

use App\Models\Turf;
use App\Models\TurfSlot;
use App\Models\TurfOverride;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Setup Test Data
$owner = User::first() ?: User::factory()->create();
$turf = Turf::create([
    'owner_id' => $owner->id,
    'name' => 'Hybrid Test Turf',
    'city' => 'Test City',
    'address' => '123 Test St',
    'sport_type' => ['Football'],
    'status' => 'active'
]);

// Clear any existing slots/overrides for this turf
$turf->slots()->delete();
$turf->overrides()->delete();

echo "Testing Hybrid Availability System...\n";

// 2. Create a Monday Template Slot (day_of_week = 1)
$turf->slots()->create([
    'start_time' => '10:00',
    'end_time' => '11:00',
    'price' => 1000,
    'day_of_week' => 1 
]);

// 3. Create an Every Day Template Slot (day_of_week = null)
$turf->slots()->create([
    'start_time' => '18:00',
    'end_time' => '19:00',
    'price' => 1200,
    'day_of_week' => null
]);

// 4. Create an Override for next Monday (Reprice)
$nextMonday = Carbon::now()->next(Carbon::MONDAY)->toDateString();
$turf->overrides()->create([
    'date' => $nextMonday,
    'start_time' => '10:00',
    'end_time' => '11:00',
    'price' => 1500, // Premium price override
]);

// 5. Create an Override for next Tuesday (Block Every Day slot)
$nextTuesday = Carbon::now()->next(Carbon::TUESDAY)->toDateString();
$turf->overrides()->create([
    'date' => $nextTuesday,
    'start_time' => '18:00',
    'end_time' => '19:00',
    'is_blocked' => true
]);

echo "Data setup complete.\n\n";

function checkAvailability($turfId, $date) {
    $controller = new \App\Http\Controllers\Api\Customer\TurfController();
    $request = new Request(['date' => $date]);
    $response = $controller->show($turfId, $request);
    return json_decode($response->getContent(), true)['slots'];
}

// Verification 1: Check Monday
echo "Checking Monday ($nextMonday):\n";
$slots = checkAvailability($turf->id, $nextMonday);
foreach ($slots as $s) {
    echo "- {$s['start_time']} to {$s['end_time']}: Price {$s['price']} (Override: " . ($s['is_override'] ? 'YES' : 'NO') . ")\n";
}

// Verification 2: Check Tuesday
echo "\nChecking Tuesday ($nextTuesday):\n";
$slots = checkAvailability($turf->id, $nextTuesday);
foreach ($slots as $s) {
    echo "- {$s['start_time']} to {$s['end_time']}: Price {$s['price']} (Override: " . ($s['is_override'] ? 'YES' : 'NO') . ")\n";
}

// Cleanup
$turf->delete();
echo "\nVerification finished.\n";
