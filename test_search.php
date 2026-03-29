<?php

/**
 * Verification script for Venue Search API
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Turf;
use Illuminate\Http\Request;

// Helper to print results
function printResults($title, $results) {
    echo "\n--- $title ---\n";
    $data = $results->getData()->data;
    if (empty($data)) {
        echo "No results found.\n";
    } else {
        foreach ($data as $turf) {
            echo "- ID: {$turf->id}, Name: {$turf->name}, City: {$turf->city}, Address: {$turf->address}\n";
        }
    }
}

// Mocking some data if needed (but assuming database has data from previous sessions)
$controller = app(App\Http\Controllers\Api\Customer\TurfController::class);

// Test 1: Search by Name (Partial)
$request = Request::create('/api/turfs', 'GET', ['search' => 'Turf']);
$response = $controller->index($request);
printResults("Search by Name: 'Turf'", $response);

// Test 2: Search by City
$request = Request::create('/api/turfs', 'GET', ['search' => 'Bhopal']);
$response = $controller->index($request);
printResults("Search by City: 'Bhopal'", $response);

// Test 3: Search by Address
$request = Request::create('/api/turfs', 'GET', ['search' => 'Street']);
$response = $controller->index($request);
printResults("Search by Address: 'Street'", $response);

// Test 4: Combined filters (City + Search)
$request = Request::create('/api/turfs', 'GET', ['city' => 'Bhopal', 'search' => 'Turf']);
$response = $controller->index($request);
printResults("City: Bhopal + Search: 'Turf'", $response);

echo "\nVerification complete.\n";
