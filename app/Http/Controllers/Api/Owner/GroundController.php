<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Turf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GroundController extends Controller
{
    /**
     * List all turfs owned by the authenticated owner.
     */
    public function index(Request $request)
    {
        $turfs = Turf::where('owner_id', $request->user()->id)->paginate(10);
        return response()->json($turfs);
    }

    /**
     * Store a new turf.
     */
    public function store(Request $request)
    {
        // Auto-wrap into arrays if they come as strings
        if ($request->has('sport_type') && is_string($request->sport_type)) {
            $request->merge(['sport_type' => [$request->sport_type]]);
        }
        if ($request->has('amenities') && is_string($request->amenities)) {
            $request->merge(['amenities' => [$request->amenities]]);
        }

        // Handle slots sent as a JSON string (common for FormData)
        if ($request->has('slots') && is_string($request->slots)) {
            $decoded = json_decode($request->slots, true);
            if (is_array($decoded)) {
                $request->merge(['slots' => $decoded]);
            }
        }

        // Normalize time formats with Carbon
        if ($request->has('slots') && is_array($request->slots)) {
            $slots = $request->slots;
            foreach ($slots as &$slot) {
                try {
                    if (!empty($slot['start_time'])) {
                        $slot['start_time'] = Carbon::parse($slot['start_time'])->format('H:i');
                    }
                    if (!empty($slot['end_time'])) {
                        $slot['end_time'] = Carbon::parse($slot['end_time'])->format('H:i');
                    }
                } catch (\Exception $e) {
                    // Let validator handle invalid strings
                }
            }
            $request->merge(['slots' => $slots]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'sport_type' => 'required|array',
            'sport_type.*' => 'string',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'city' => 'required|string',
            'address' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'slots' => 'nullable|array',
            'slots.*.start_time' => 'required|date_format:H:i',
            'slots.*.end_time' => 'required|date_format:H:i|after:slots.*.start_time',
            'slots.*.price' => 'required|numeric',
            'slots.*.sport_type' => 'nullable|string',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            
            // Handle both single file and array of files
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $image) {
                $path = $image->store('turf_images', 'public');
                $imagePaths[] = asset('storage/' . $path);
            }
        }

        $turf = Turf::create([
            'owner_id' => $request->user()->id,
            'name' => $request->name,
            'sport_type' => $request->sport_type,
            'description' => $request->description,
            'amenities' => $request->amenities,
            'city' => $request->city,
            'address' => $request->address,
            'images' => $imagePaths,
        ]);

        if ($request->has('slots')) {
            foreach ($request->slots as $slotData) {
                $turf->slots()->create([
                    'start_time' => $slotData['start_time'],
                    'end_time' => $slotData['end_time'],
                    'price' => $slotData['price'],
                    'sport_type' => $slotData['sport_type'] ?? null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Turf created successfully',
            'turf' => $turf->load('slots')
        ], 201);
    }

    /**
     * Display the specified turf.
     */
    public function show($id, Request $request)
    {
        $turf = Turf::with('slots')->where('owner_id', $request->user()->id)->findOrFail($id);
        return response()->json($turf);
    }

    public function update(Request $request, $id)
    {
        $turf = Turf::where('owner_id', $request->user()->id)->findOrFail($id);

        // Auto-wrap into arrays if they come as strings
        if ($request->has('sport_type') && is_string($request->sport_type)) {
            $request->merge(['sport_type' => [$request->sport_type]]);
        }
        if ($request->has('amenities') && is_string($request->amenities)) {
            $request->merge(['amenities' => [$request->amenities]]);
        }

        // Handle slots sent as a JSON string (common for FormData)
        if ($request->has('slots') && is_string($request->slots)) {
            $decoded = json_decode($request->slots, true);
            if (is_array($decoded)) {
                $request->merge(['slots' => $decoded]);
            }
        }

        // Normalize time formats with Carbon
        if ($request->has('slots') && is_array($request->slots)) {
            $slots = $request->slots;
            foreach ($slots as &$slot) {
                try {
                    if (!empty($slot['start_time'])) {
                        $slot['start_time'] = Carbon::parse($slot['start_time'])->format('H:i');
                    }
                    if (!empty($slot['end_time'])) {
                        $slot['end_time'] = Carbon::parse($slot['end_time'])->format('H:i');
                    }
                } catch (\Exception $e) {
                    // Let validator handle invalid strings
                }
            }
            $request->merge(['slots' => $slots]);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'sport_type' => 'sometimes|array',
            'sport_type.*' => 'string',
            'description' => 'nullable|string',
            'city' => 'sometimes|string',
            'address' => 'sometimes|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'amenities' => 'nullable|array',
            'clear_images' => 'sometimes|boolean',
            'status' => 'sometimes|in:active,inactive',
            'slots' => 'nullable|array',
            'slots.*.start_time' => 'required|date_format:H:i',
            'slots.*.end_time' => 'required|date_format:H:i|after:slots.*.start_time',
            'slots.*.price' => 'required|numeric',
            'slots.*.sport_type' => 'nullable|string',
        ]);

        $data = $request->except(['images', 'image', 'clear_images', 'slots']);
        $imagePaths = ($request->clear_images) ? [] : ($turf->images ?? []);

        if ($request->hasFile('images')) {
            $files = $request->file('images');
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $image) {
                $path = $image->store('turf_images', 'public');
                $imagePaths[] = asset('storage/' . $path);
            }
            $data['images'] = $imagePaths;
        } elseif ($request->clear_images) {
            $data['images'] = [];
        }

        $turf->update($data);

        // Update slots
        if ($request->has('slots')) {
            $turf->slots()->delete();
            foreach ($request->slots as $slotData) {
                $turf->slots()->create([
                    'start_time' => $slotData['start_time'],
                    'end_time' => $slotData['end_time'],
                    'price' => $slotData['price'],
                    'sport_type' => $slotData['sport_type'] ?? null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Turf updated successfully',
            'turf' => $turf->load('slots')
        ]);
    }

    /**
     * Remove a specific image from the gallery.
     */
    public function removeImage(Request $request, $id)
    {
        $turf = Turf::where('owner_id', $request->user()->id)->findOrFail($id);
        
        $request->validate([
            'image_url' => 'required|string'
        ]);

        $imagePaths = $turf->images ?? [];
        $urlToRemove = $request->image_url;

        if (($key = array_search($urlToRemove, $imagePaths)) !== false) {
            // Remove from array
            unset($imagePaths[$key]);
            
            // Delete from physical storage
            $basename = basename($urlToRemove);
            \Illuminate\Support\Facades\Storage::disk('public')->delete('turf_images/' . $basename);
            
            // Re-index and save
            $turf->update(['images' => array_values($imagePaths)]);

            return response()->json([
                'message' => 'Image removed successfully',
                'images' => $turf->images
            ]);
        }

        return response()->json(['message' => 'Image not found in gallery'], 404);
    }

    /**
     * Remove the specified turf.
     */
    public function destroy($id, Request $request)
    {
        $turf = Turf::where('owner_id', $request->user()->id)->findOrFail($id);
        $turf->delete();

        return response()->json(['message' => 'Turf deleted successfully']);
    }
}
