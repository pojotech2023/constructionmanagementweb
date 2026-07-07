<?php

namespace App\Http\Controllers\API;

use App\Exports\OtherUtilitiesExport;
use App\Http\Controllers\Controller;
use App\Models\OtherUtilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class OtherUtilitiesController extends Controller
{
    public function index($id)
    {
        $utilities = OtherUtilities::with('site:id,site_name')
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

        $utilities = OtherUtilities::create([
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
        $utility = OtherUtilities::find($id);

        if (!$utility) {
            return response()->json([
                'status' => false,
                'message' => 'Utility not found.',
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
            'message' => 'Other Utilities Updated Successfully!.',
        ]);
    }

    public function destroy($id)
    {
        $utility = OtherUtilities::find($id);

        if (!$utility) {
            return response()->json([
                'status'  => false,
                'message' => 'Utility not found.',
            ], 404);
        }

        $utility->delete();

        return response()->json([
            'response code' => 200,
            'status'  => true,
            'message' => 'Other Utilities Deleted Successfully!.',
        ]);
    }

    public function export(Request $request, $id)
    {
        $fileName = 'other_utilities_' . $id . '_' . time() . '.xlsx';
        $filePath = 'exports/' . $fileName;

        try {
            Excel::store(
                new OtherUtilitiesExport($id, $request->from_date, $request->to_date),
                $filePath,
                'public'
            );

            return response()->json([
                'status'       => true,
                'message'      => 'Other Utilities Excel file generated successfully.',
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
