<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::orderBy('id', 'desc')->get();
        return response()->json([
            'respnse code' => 200,
            'data' => $properties,
            'status' => true,
            'message' => 'Properties fetched successfully!',
        ]);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'location' => 'required',
            'type' => 'required',
            'amount' => 'required|numeric',
            'remarks' => 'required',
            'image' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }

        // Upload Image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('properties', 'public');
        }

        $property = Property::create([
            'name' => $request->name,
            'location' => $request->location,
            'type' => $request->type,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
            'image' => $imagePath,
            'created_by' => auth('api')->id(),
        ]);

        // $message = "New Property Added\n" .
        //     "Name: {$property->name}\n" .
        //     "Location: {$property->location}\n" .
        //     "Type: {$property->type}\n" .
        //     "Amount: {$property->amount}\n" .
        //     "Remarks: {$property->remarks}";

        // if ($property->image) {
        //     $imgUrl = asset('storage/' . $property->image);
        //     $message .= "\n\n *Image:* {$imgUrl}";
        // }

        // $agents = Agent::where('is_inactive', 0)->get();
        // $urls = [];

        // foreach ($agents as $agent) {
        //     $urls[] = "https://wa.me/{$agent->mobile}?text=" . urlencode($message);
        // }

        return response()->json([
            'response code' => 200,
            'data' => $property,
            'message' => 'Property created successfully!',
        ]);

    }
}
