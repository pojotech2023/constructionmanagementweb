<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MaterialTypeController extends Controller
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

        if (MaterialType::where('slug', $slug)->exists()) {
            return redirect()->back()->withErrors(['name' => 'This material type already exists.'])->withInput();
        }

        $imagePath = $request->file('image')->store('material_types', 'public');

        MaterialType::create([
            'name' => $request->name,
            'slug' => $slug,
            'image' => $imagePath,
            'created_by' => auth('admin')->id(),
        ]);

        return redirect()->back()->with('success', 'Material type added successfully!');
    }

    public function delete($id)
    {
        $materialType = MaterialType::findOrFail($id);

        if (!empty($materialType->image)) {
            try {
                Storage::disk('public')->delete($materialType->image);
            } catch (\Exception $e) {}
        }

        $materialType->delete();

        return redirect()->back()->with('success', 'Material type deleted successfully!');
    }

    public function hideFixed(string $slug)
    {
        Setting::hideMaterialType($slug);

        return redirect()->back()->with('success', 'Material type removed from the grid.');
    }
}
