<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OtherUtilitiesSub;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OtherUtilitiesSubController extends Controller
{
    public function index($id)
    {
        $utilities = OtherUtilitiesSub::with('site')
            ->where('site_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        $siteId = $id;

        return view('admin.menus.subcontractor.view_utilities', compact('utilities', 'siteId'));
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

        $utilities = OtherUtilitiesSub::create([
            'site_id' => $request->site_id,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
            'image' => $imagePath,
            'created_by'  => auth('admin')->id(),
        ]);

        // If a date is provided, set created_at so record can be backdated
        if ($request->filled('date')) {
            $utilities->created_at = $request->date;
            $utilities->save();
        }

        return redirect()->back()->with('success', 'Others utility created successfully!');
    }

    public function update(Request $request, $id)
    {
        $util = OtherUtilitiesSub::findOrFail($id);

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
            if (!empty($util->image)) {
                Storage::disk('public')->delete($util->image);
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
        $util = OtherUtilitiesSub::findOrFail($id);

        if (!empty($util->image)) {
            Storage::disk('public')->delete($util->image);
        }

        $util->delete();

        return redirect()->back()->with('success', 'Others utility deleted successfully!');
    }

    public function export(Request $request, $id)
    {
        $query = OtherUtilitiesSub::where('site_id', $id);

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->from_date)->toDateString());
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->to_date)->toDateString());
        }

        $utilities = $query->orderBy('id', 'desc')->get();

        $filename = 'subcontractor_other_utilities_' . $id . '_' . now()->format('Ymd_His') . '.csv';
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
