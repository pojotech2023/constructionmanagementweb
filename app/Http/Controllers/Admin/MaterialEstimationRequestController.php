<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialEstimationRequest;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaterialEstimationRequestController extends Controller
{
    public function getForm($siteId)
    {
        $site = Site::findOrFail($siteId);

        $requests = MaterialEstimationRequest::where('site_id', $siteId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.menus.material_estimation.material_estimation_add', compact('site', 'requests'));
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'particular' => 'required|array',
            'particular.*' => 'required|string',
            'unit' => 'nullable|array',
            'date' => 'required|array',
            'date.*' => 'required|date',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        foreach ($request->particular as $index => $particular) {
            MaterialEstimationRequest::create([
                'site_id' => $request->site_id,
                'particular' => $particular,
                'unit' => $request->unit[$index] ?? null,
                'date' => $request->date[$index],
                'created_by' => auth('admin')->id(),
            ]);
        }

        return redirect()->route('materialEstimation.form', $request->site_id)
            ->with('success', 'Material estimation request submitted successfully.');
    }

    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'particular' => 'required|string',
            'unit' => 'nullable|string',
            'date' => 'required|date',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $materialEstimationRequest = MaterialEstimationRequest::findOrFail($id);
        $materialEstimationRequest->update([
            'particular' => $request->particular,
            'unit' => $request->unit,
            'date' => $request->date,
            'updated_by' => auth('admin')->id(),
        ]);

        return redirect()->route('materialEstimation.form', $materialEstimationRequest->site_id)
            ->with('success', 'Material estimation request updated successfully.');
    }

    public function delete($id)
    {
        $materialEstimationRequest = MaterialEstimationRequest::findOrFail($id);
        $siteId = $materialEstimationRequest->site_id;
        $materialEstimationRequest->delete();

        return redirect()->route('materialEstimation.form', $siteId)
            ->with('success', 'Material estimation request deleted successfully.');
    }
}
