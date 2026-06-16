<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtherUtilities;
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

        return view('admin.menus.site_management.view_utilities', compact('utilities'));
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

}
