<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaterialType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MaterialTypeController extends Controller
{
    // Admin: add a new dynamic material type/card
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

        if (MaterialType::where('slug', $slug)->exists()) {
            return response()->json([
                'status' => 'error',
                'errors' => ['name' => ['This material type already exists.']],
            ], 422);
        }

        $imagePath = $request->file('image')->store('material_types', 'public');

        $materialType = MaterialType::create([
            'name' => $request->name,
            'slug' => $slug,
            'image' => $imagePath,
            'created_by' => auth('api')->id(),
        ]);

        return response()->json([
            'response_code' => 200,
            'status' => true,
            'message' => 'Material type added successfully.',
            'data' => [
                'id' => $materialType->id,
                'name' => $materialType->name,
                'slug' => $materialType->slug,
                'image_url' => asset('storage/' . $materialType->image),
            ],
        ]);
    }

    // Admin: hard-delete a dynamically-added material type/card
    public function delete($id)
    {
        $materialType = MaterialType::findOrFail($id);

        if (!empty($materialType->image)) {
            try {
                Storage::disk('public')->delete($materialType->image);
            } catch (\Exception $e) {
            }
        }

        $materialType->delete();

        return response()->json([
            'response_code' => 200,
            'status' => true,
            'message' => 'Material type deleted successfully.',
        ]);
    }

    // Admin: hide a fixed (built-in) material card from the grid
    public function hideFixed(string $slug)
    {
        Setting::hideMaterialType($slug);

        return response()->json([
            'response_code' => 200,
            'status' => true,
            'message' => 'Material type removed from the grid.',
        ]);
    }
}
