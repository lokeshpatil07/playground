<?php

use App\Models\Turf;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\nTesting Nearby Search with Filter ('search' => 'Modern')...\n";

$controller = new App\Http\Controllers\Api\Customer\TurfController();

// 1. Test Search Filter
$requestSearch = new Request([
    'latitude' => 19.0760,
    'longitude' => 72.8777,
    'search' => 'Modern'
]);
$responseSearch = $controller->nearby($requestSearch);
$dataSearch = json_decode($responseSearch->getContent(), true);

echo "Search result count (should be 1 for 'Modern'): " . count($dataSearch['data']) . "\n";
if (count($dataSearch['data']) > 0) {
    echo "First result: " . $dataSearch['data'][0]['name'] . "\n";
}

// 2. Test Pagination structure
echo "\nPagination structure check:\n";
echo "Current Page: " . $dataSearch['current_page'] . "\n";
echo "Total Items: " . $dataSearch['total'] . "\n";

// 3. Test City Filter
echo "\nTesting Nearby Search with City ('city' => 'Jalgaon')...\n";
$requestCity = new Request([
    'latitude' => 19.0760,
    'longitude' => 72.8777,
    'city' => 'Jalgaon'
]);
$responseCity = $controller->nearby($requestCity);
$dataCity = json_decode($responseCity->getContent(), true);
echo "City result count (should be 1 for 'Jalgaon'): " . count($dataCity['data']) . "\n";
if (count($dataCity['data']) > 0) {
    echo "First result: " . $dataCity['data'][0]['name'] . " (City: " . $dataCity['data'][0]['city'] . ")\n";
}
