<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OtherUtilitiesSub;
use Illuminate\Support\Facades\Validator;

class OtherUtilitiesSubController extends Controller
{
     public function index($id)
    {
        $utilities = OtherUtilitiesSub::with('site:id,site_name')
            ->where('site_id', $id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($utility){
                return [
                    'id' => $utility->id,
                    'site_id' => $utility->site_id,
                    'site_name' =>$utility->site->site_name,
                    'amount' => $utility->amount,
                    'remarks' => $utility->remarks,
                    'image' => $utility->image,
                    'created_at' => $utility->created_at,
                    'updated_at' => $utility->updated_at
                ];
            });

        return response()->json([
            'response code' => 200,
            'data' => $utilities,
            'status' => true,
            'message' => 'Other Utilities Feteched Successfully!.'
        ]);
    }


    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'site_id'     => 'required|exists:sites,id',
            'amount'      => 'required|string',
            'remarks'     => 'required|string',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp'
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
            $imagePath = $request->file('image')->store('other_utilities', 'public');
        }

        $utilities = OtherUtilitiesSub::create([
            'site_id' => $request->site_id,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
            'image' => $imagePath,
            'created_by' => auth('api')->id(),
        ]);

        return response()->json([
            'response code' => 200,
            'data' => $utilities,
            'status' => true,
            'message' => 'Other Utilities Added Successfully!.'
        ]);
    }
}
