<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OtherUtilitiesSub;
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

        return view('admin.menus.subcontractor.view_utilities', compact('utilities'));
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
}
