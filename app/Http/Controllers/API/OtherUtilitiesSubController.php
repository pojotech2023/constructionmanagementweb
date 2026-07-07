<?php

namespace App\Http\Controllers\API;

use App\Exports\OtherUtilitiesSubExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OtherUtilitiesSub;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

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

    public function update(Request $request, $id)
    {
        $utility = OtherUtilitiesSub::find($id);

        if (!$utility) {
            return response()->json([
                'status' => false,
                'message' => 'Sub Utility not found.',
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'amount'  => 'required|string',
            'remarks' => 'required|string',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors(),
            ], 422);
        }

        if ($request->hasFile('image')) {
            $utility->image = $request->file('image')->store('other_utilities', 'public');
        }

        $utility->amount  = $request->amount;
        $utility->remarks = $request->remarks;
        $utility->save();

        return response()->json([
            'response code' => 200,
            'data'    => $utility,
            'status'  => true,
            'message' => 'Sub Utilities Updated Successfully!.',
        ]);
    }

    public function destroy($id)
    {
        $utility = OtherUtilitiesSub::find($id);

        if (!$utility) {
            return response()->json([
                'status'  => false,
                'message' => 'Sub Utility not found.',
            ], 404);
        }

        $utility->delete();

        return response()->json([
            'response code' => 200,
            'status'  => true,
            'message' => 'Sub Utilities Deleted Successfully!.',
        ]);
    }

    public function export(Request $request, $id)
    {
        $fileName = 'other_sub_utilities_' . $id . '_' . time() . '.xlsx';
        $filePath = 'exports/' . $fileName;

        try {
            Excel::store(
                new OtherUtilitiesSubExport($id, $request->from_date, $request->to_date),
                $filePath,
                'public'
            );

            return response()->json([
                'status'       => true,
                'message'      => 'Sub Utilities Excel file generated successfully.',
                'download_url' => asset('storage/' . $filePath),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to generate Excel: ' . $e->getMessage(),
            ], 500);
        }
    }
}
