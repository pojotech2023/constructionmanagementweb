<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SubcontractorType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubcontractorTypeController extends Controller
{
    // Admin: add a new subcontractor type/card ("+ Add SubContractor")
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors(),
            ], 422);
        }

        $slug = Str::slug($request->name, '');

        if (SubcontractorType::where('slug', $slug)->exists()) {
            return response()->json([
                'status' => 'error',
                'errors' => ['name' => ['This subcontractor type already exists.']],
            ], 422);
        }

        $imagePath = $request->file('image')->store('subcontractor_types', 'public');

        $subcontractorType = SubcontractorType::create([
            'name' => $request->name,
            'slug' => $slug,
            'image' => $imagePath,
            'created_by' => auth('api')->id(),
        ]);

        return response()->json([
            'response_code' => 200,
            'status' => true,
            'message' => 'Subcontractor type added successfully.',
            'data' => [
                'id' => $subcontractorType->id,
                'name' => $subcontractorType->name,
                'slug' => $subcontractorType->slug,
                'image_url' => asset('storage/' . $subcontractorType->image),
            ],
        ]);
    }

    // Admin: hard-delete a dynamically-added subcontractor type/card (the × button)
    public function delete($id)
    {
        $subcontractorType = SubcontractorType::findOrFail($id);

        if (!empty($subcontractorType->image)) {
            try {
                Storage::disk('public')->delete($subcontractorType->image);
            } catch (\Exception $e) {
            }
        }

        $subcontractorType->delete();

        return response()->json([
            'response_code' => 200,
            'status' => true,
            'message' => 'Subcontractor type deleted successfully.',
        ]);
    }

    // Admin: hide a fixed (built-in) subcontractor card from the grid (the × button)
    public function hideFixed(string $slug)
    {
        Setting::hideSubcontractorType($slug);

        return response()->json([
            'response_code' => 200,
            'status' => true,
            'message' => 'Subcontractor type removed from the grid.',
        ]);
    }
}
