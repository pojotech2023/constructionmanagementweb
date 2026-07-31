<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaterialEstimationRequest;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaterialEstimationRequestController extends Controller
{
    public function index($siteId)
    {
        $site = Site::find($siteId);

        if (!$site) {
            return response()->json(['status' => false, 'message' => 'Site not found.'], 404);
        }

        $requests = MaterialEstimationRequest::where('site_id', $siteId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Material estimation requests fetched successfully.',
            'data' => [
                'site' => $site,
                'requests' => $requests,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'particular' => 'required|array',
            'particular.*' => 'required|string',
            'quantity' => 'nullable|array',
            'quantity.*' => 'nullable|numeric',
            'unit' => 'nullable|array',
            'date' => 'required|array',
            'date.*' => 'required|date',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => false, 'errors' => $validate->errors()], 422);
        }

        $created = [];
        foreach ($request->particular as $index => $particular) {
            $created[] = MaterialEstimationRequest::create([
                'site_id' => $request->site_id,
                'particular' => $particular,
                'quantity' => $request->quantity[$index] ?? null,
                'unit' => $request->unit[$index] ?? null,
                'date' => $request->date[$index],
                'created_by' => auth('api')->id(),
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Material estimation request submitted successfully.',
            'data' => $created,
        ]);
    }

    public function show($id)
    {
        $materialEstimationRequest = MaterialEstimationRequest::with('site')->find($id);

        if (!$materialEstimationRequest) {
            return response()->json(['status' => false, 'message' => 'Material estimation request not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Material estimation request fetched successfully.',
            'data' => $materialEstimationRequest,
        ]);
    }

    public function update(Request $request, $id)
    {
        $materialEstimationRequest = MaterialEstimationRequest::find($id);

        if (!$materialEstimationRequest) {
            return response()->json(['status' => false, 'message' => 'Material estimation request not found.'], 404);
        }

        $validate = Validator::make($request->all(), [
            'particular' => 'required|string',
            'quantity' => 'nullable|numeric',
            'unit' => 'nullable|string',
            'date' => 'required|date',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => false, 'errors' => $validate->errors()], 422);
        }

        $materialEstimationRequest->update([
            'particular' => $request->particular,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'date' => $request->date,
            'updated_by' => auth('api')->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Material estimation request updated successfully.',
            'data' => $materialEstimationRequest,
        ]);
    }

    public function destroy($id)
    {
        $materialEstimationRequest = MaterialEstimationRequest::find($id);

        if (!$materialEstimationRequest) {
            return response()->json(['status' => false, 'message' => 'Material estimation request not found.'], 404);
        }

        $materialEstimationRequest->delete();

        return response()->json([
            'status' => true,
            'message' => 'Material estimation request deleted successfully.',
        ]);
    }
}
