<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientProfileController extends Controller
{
    // GET /client-profile — returns the logged-in client's own profile
    public function show()
    {
        $customer = auth('customer')->user();

        if (!$customer instanceof Customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. This endpoint is for client accounts only.',
            ], 403);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Client profile fetched successfully.',
            'data'    => $customer->load('site'),
        ]);
    }

    // POST /client-profile-update — lets the logged-in client edit their own profile
    public function update(Request $request)
    {
        $customer = auth('customer')->user();

        if (!$customer instanceof Customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. This endpoint is for client accounts only.',
            ], 403);
        }

        $validate = Validator::make($request->all(), [
            'name'          => 'nullable|string|max:255',
            'email'         => 'nullable|email|unique:customers,email,' . $customer->id,
            'dob'           => 'nullable|date',
            'marriage_date' => 'nullable|date',
            'address'       => 'nullable|string|max:255',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $validate->errors(),
            ], 422);
        }

        // Only self-editable fields — site_id, mobile_no (login identity),
        // is_inactive, created_by/updated_by stay admin-controlled.
        $customer->update($validate->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Client profile updated successfully.',
            'data'    => $customer->fresh('site'),
        ]);
    }
}
