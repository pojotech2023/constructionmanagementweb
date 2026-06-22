<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtherUtilities;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OtherUtilitiesController extends Controller
{
    public function index($id)
    {
        $utilities = OtherUtilities::with('site')
            ->where('site_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        $siteId = $id;

        return view('admin.menus.site_management.view_utilities', compact('utilities', 'siteId'));
    }


    public function store(Request $request)
    {
        //dd($request->all());
        $validate = Validator::make($request->all(), [
            'site_id'     => 'required|exists:sites,id',
            'amount'      => 'required|numeric',
            'date'        => 'nullable|date',
            'remarks'     => 'required|string',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        // Upload Image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('other_utilities', 'public');
        }

        $vendor = OtherUtilities::create([
            'site_id' => $request->site_id,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
            'image' => $imagePath,
            'created_by'  => auth('admin')->id(),
        ]);

        // If user provided a date, set created_at to that date (allow backdate)
        if ($request->filled('date')) {
            $vendor->created_at = $request->date;
            $vendor->save();
        }

        return redirect()->back()->with('success', 'Others utility created successfully!');
    }

    public function update(Request $request, $id)
    {
        $util = OtherUtilities::findOrFail($id);

        $validate = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'remarks' => 'nullable|string',
            'date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        if ($request->hasFile('image')) {
            // remove old image if exist
            if (!empty($util->image)) {
                try {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($util->image);
                } catch (\Exception $e) {}
            }
            $util->image = $request->file('image')->store('other_utilities', 'public');
        }

        $util->amount = $request->amount;
        $util->remarks = $request->remarks;
        if ($request->filled('date')) {
            $util->created_at = $request->date;
        }
        $util->updated_by = auth('admin')->id();
        $util->save();

        return redirect()->back()->with('success', 'Others utility updated successfully!');
    }

    public function delete($id)
    {
        $util = OtherUtilities::findOrFail($id);
        // delete image
        if (!empty($util->image)) {
            try { \Illuminate\Support\Facades\Storage::disk('public')->delete($util->image); } catch (\Exception $e) {}
        }
        $util->delete();
        return redirect()->back()->with('success', 'Others utility deleted successfully!');
    }

    public function export(Request $request, $id)
    {
        $query = OtherUtilities::where('site_id', $id);

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->from_date)->toDateString());
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->to_date)->toDateString());
        }

        $utilities = $query->orderBy('id', 'desc')->get();

        $filename = 'material_other_utilities_' . $id . '_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($utilities) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['S.No', 'Date', 'Amount', 'Remarks', 'Image']);

            foreach ($utilities as $index => $utility) {
                fputcsv($file, [
                    $index + 1,
                    $utility->created_at ? Carbon::parse($utility->created_at)->format('d-m-Y') : '',
                    number_format((float) $utility->amount, 2, '.', ''),
                    $utility->remarks,
                    $utility->image ? asset('storage/' . $utility->image) : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}
