<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Turf;
use Illuminate\Http\Request;

class TurfController extends Controller
{
    /**
     * List all available turfs (can be filtered by city).
     */
    public function index(Request $request)
    {
        $query = Turf::with('slots')->where('status', 'active');

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        if ($request->has('sport_type')) {
            $query->whereJsonContains('sport_type', $request->sport_type);
        }

        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                  ->orWhere('city', 'LIKE', $searchTerm)
                  ->orWhere('address', 'LIKE', $searchTerm)
                  ->orWhere('description', 'LIKE', $searchTerm);
            });
        }

        return response()->json($query->latest()->paginate(10));
    }

    /**
     * Show details of a specific turf.
     */
    public function show($id)
    {
        $turf = Turf::with(['slots', 'reviews.user:id,name'])->findOrFail($id);
        return response()->json($turf);
    }

    /**
     * Find nearby turfs based on latitude, longitude and optional radius.
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric',
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;
        $radius = $request->radius;

        $query = Turf::with('slots')->selectRaw("
            *,
            (6371 * acos(
                cos(radians(?)) *
                cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(latitude))
            )) AS distance
        ", [$lat, $lng, $lat])
        ->where('status', 'active');

        if ($radius) {
            $query->having("distance", "<", $radius);
        }

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        if ($request->has('sport_type')) {
            $query->whereJsonContains('sport_type', $request->sport_type);
        }

        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                  ->orWhere('city', 'LIKE', $searchTerm)
                  ->orWhere('address', 'LIKE', $searchTerm)
                  ->orWhere('description', 'LIKE', $searchTerm);
            });
        }

        return response()->json($query->orderBy("distance")->paginate(10));
    }
}
