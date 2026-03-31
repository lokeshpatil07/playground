<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Turf;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
    public function show($id, Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $turf = Turf::with(['reviews.user:id,name'])->findOrFail($id);
        
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        
        // 1. Get Template Slots
        $templateSlots = $turf->slots()
            ->where(function($q) use ($dayOfWeek) {
                $q->where('day_of_week', $dayOfWeek)
                  ->orWhereNull('day_of_week');
            })->get();
            
        // 2. Get Overrides
        $overrides = $turf->overrides()->where('date', $date)->get();
        
        // 3. Get Confirmed Bookings
        $bookings = $turf->bookings()
            ->whereDate('start_time', $date)
            ->where('status', 'confirmed')
            ->get();
            
        $finalSlots = [];
        
        // Match templates with overrides and existing bookings
        foreach ($templateSlots as $slot) {
            $override = $overrides->where('start_time', $slot->start_time)
                                 ->where('end_time', $slot->end_time)
                                 ->first();
            
            if ($override) {
                if ($override->is_blocked) continue;
                
                $finalSlots[] = [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'price' => $override->price ?? $slot->price,
                    'sport_type' => $override->sport_type ?? $slot->sport_type,
                    'is_available' => !$bookings->where('start_time', Carbon::parse($date . ' ' . $slot->start_time))->count(),
                    'is_override' => true
                ];
            } else {
                $finalSlots[] = [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'price' => $slot->price,
                    'sport_type' => $slot->sport_type,
                    'is_available' => !$bookings->where('start_time', Carbon::parse($date . ' ' . $slot->start_time))->count(),
                    'is_override' => false
                ];
            }
        }
        
        // Add "pure" overrides that don't match any template
        foreach ($overrides as $override) {
            if ($override->is_blocked) continue;
            
            $existsInFinal = collect($finalSlots)->contains(function($s) use ($override) {
                return $s['start_time'] == $override->start_time && $s['end_time'] == $override->end_time;
            });
            
            if (!$existsInFinal) {
                $finalSlots[] = [
                    'id' => null, 
                    'start_time' => $override->start_time,
                    'end_time' => $override->end_time,
                    'price' => $override->price,
                    'sport_type' => $override->sport_type,
                    'is_available' => !$bookings->where('start_time', Carbon::parse($date . ' ' . $override->start_time))->count(),
                    'is_override' => true
                ];
            }
        }

        $turfArray = $turf->toArray();
        $turfArray['slots'] = collect($finalSlots)->sortBy('start_time')->values();
        $turfArray['date'] = $date;
        
        return response()->json($turfArray);
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
