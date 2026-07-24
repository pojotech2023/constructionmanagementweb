<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubcontractorType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubcontractorTypeController extends Controller
{
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $slug = Str::slug($request->name, '');

        if (SubcontractorType::where('slug', $slug)->exists()) {
            return redirect()->back()->withErrors(['name' => 'This subcontractor type already exists.'])->withInput();
        }

        $imagePath = $request->file('image')->store('subcontractor_types', 'public');

        SubcontractorType::create([
            'name' => $request->name,
            'slug' => $slug,
            'image' => $imagePath,
            'created_by' => auth('admin')->id(),
        ]);

        return redirect()->back()->with('success', 'Subcontractor type added successfully!');
    }

    public function delete($id)
    {
        $subcontractorType = SubcontractorType::findOrFail($id);

        if (!empty($subcontractorType->image)) {
            try {
                Storage::disk('public')->delete($subcontractorType->image);
            } catch (\Exception $e) {}
        }

        $subcontractorType->delete();

        return redirect()->back()->with('success', 'Subcontractor type deleted successfully!');
    }

    public function hideFixed(string $slug)
    {
        Setting::hideSubcontractorType($slug);

        return redirect()->back()->with('success', 'Subcontractor type removed from the grid.');
    }
}
