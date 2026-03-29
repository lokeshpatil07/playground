<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get current application settings.
     */
    public function index()
    {
        return response()->json([
            'settings' => [
                'admin_commission' => \App\Models\Setting::get('admin_commission', 10),
                'razorpay_mode' => \App\Models\Setting::get('razorpay_mode', 'test'),
                'razorpay_key' => \App\Models\Setting::get('razorpay_key'),
                'razorpay_secret' => \App\Models\Setting::get('razorpay_secret'),
            ]
        ]);
    }

    /**
     * Update application settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'admin_commission' => 'nullable|numeric|min:0|max:100',
            'razorpay_mode' => 'nullable|in:test,live',
            'razorpay_key' => 'nullable|string',
            'razorpay_secret' => 'nullable|string',
        ]);

        $fields = [
            'admin_commission', 
            'razorpay_mode', 
            'razorpay_key', 
            'razorpay_secret'
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                \App\Models\Setting::set($field, $request->get($field));
            }
        }

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => [
                'admin_commission' => \App\Models\Setting::get('admin_commission', 10),
                'razorpay_mode' => \App\Models\Setting::get('razorpay_mode', 'test'),
                'razorpay_key' => \App\Models\Setting::get('razorpay_key'),
            ]
        ]);
    }
}
