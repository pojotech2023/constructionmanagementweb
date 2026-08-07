<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Site;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'mobile_no' => 'required|numeric|digits:10',
            'password'  => 'required|min:6'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()
            ], 422);
        }

        $user = User::where('mobile_no', $request->mobile_no)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('authToken')->plainTextToken;

            $totalSites = Site::count();
            $activeSites = Site::where('is_inactive', 0)->count();
            $siteLimit = Setting::getCurrentPlan()['site_limit'];
            $remainingSites = max(0, $siteLimit - $activeSites);

            return response()->json([
                'data' => [
                    'response code' => 200,
                    'token' => $token,
                    'user' => $user,
                    'role' => $user->roles->first()->role_name ?? null,
                    'status' => true,
                    'message' => 'Login successful',
                    'total_sites' => $totalSites,
                    'active_sites' => $activeSites,
                    'remaining_sites' => $remainingSites,
                    'site_limit' => $siteLimit,
                    'plan_name' => Setting::getCurrentPlan()['name'],
                ]
            ]);
        }

        return response()->json([
            'response code' => 401,
            'status' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete(); // Deletes the current token
        }

        return response()->json([
            'response code' => 200,
            'status' => true,
            'message' => 'User successfully logged out'
        ]);
    }
 public function loginWithMobile(Request $request)
{
    $request->validate([
        'mobile_no' => 'required|digits:10'
    ]);

    $customer = \App\Models\Customer::with('site')->where('mobile_no', $request->mobile_no)->first();

    if (!$customer) {
        return response()->json([
            'status' => false,
            'message' => 'Customer not found'
        ], 404);
    }

    // Issue a Sanctum token so the client app can call authenticated
    // endpoints (e.g. /save-device-token) the same way admin/supervisor do.
    $token = $customer->createToken('authToken')->plainTextToken;

    return response()->json([
        'status' => true,
        'message' => 'Customer found',
        'data' => [
            'token' => $token,
            'customer' => $customer,
            'site' => $customer->site   // site record from site_id
        ]
    ]);
}


}
