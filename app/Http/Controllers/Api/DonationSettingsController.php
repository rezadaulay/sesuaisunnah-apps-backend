<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonationSettings;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DonationSettingsController extends Controller
{
    /**
     * Get all donation settings
     */
    public function index(): JsonResponse
    {
        $settings = DonationSettings::all();
        
        return response()->json([
            'success' => true,
            'data' => $settings,
            'message' => 'Donation settings retrieved successfully'
        ]);
    }

    /**
     * Get active donation settings
     */
    public function getActive(): JsonResponse
    {
        $settings = DonationSettings::where('is_active', true)->first();
        
        if (!$settings) {
            return response()->json([
                'success' => false,
                'message' => 'No active donation settings found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $settings,
            'message' => 'Active donation settings retrieved successfully'
        ]);
    }
}
