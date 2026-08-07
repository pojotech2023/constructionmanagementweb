<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\DeviceToken;

class DeviceTokenController extends Controller
{
     public function store(Request $request)
    {
        //dd($request->all());
        try {
            $validate = Validator::make($request->all(), [
                'token' => 'required|string'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validate->errors()
                ], 422);
            }
            
            // Route accepts either guard (admin/supervisor via 'api', client via 'customer')
            $authUser = auth('api')->user() ?? auth('customer')->user();

            if (!$authUser) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Admin/Supervisor tokens resolve to App\Models\User, client
            // tokens resolve to App\Models\Customer — store against whichever
            // column matches so the FK stays valid for both.
            $ownerColumn = $authUser instanceof \App\Models\Customer ? 'customer_id' : 'user_id';

            // Check if token already exists
            $existing = DeviceToken::where($ownerColumn, $authUser->id)
            ->where('device_token', $request->token)
            ->first();

            if (!$existing) {
                DeviceToken::firstOrCreate([
                    $ownerColumn => $authUser->id,
                    'device_token' => $request->token,
                ], [
                    'device_type' => 'mobile',
                ]);
             }
            return response()->json(['message' => 'Token saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }
}
