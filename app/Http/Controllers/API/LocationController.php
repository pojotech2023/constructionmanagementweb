<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validate->errors(),
            ], 422);
        }

        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_updated_at' => now(),
        ]);

        return response()->json(['message' => 'Location saved successfully']);
    }
}
