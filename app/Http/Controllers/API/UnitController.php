<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('name')->get();

        return response()->json([
            'status' => true,
            'message' => 'Units fetched successfully.',
            'data' => $units,
        ]);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:units,name',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => false, 'errors' => $validate->errors()], 422);
        }

        $unit = Unit::create([
            'name' => $request->name,
            'created_by' => auth('api')->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Unit added successfully.',
            'data' => $unit,
        ]);
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['status' => false, 'message' => 'Unit not found.'], 404);
        }

        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:units,name,' . $unit->id,
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => false, 'errors' => $validate->errors()], 422);
        }

        $unit->update([
            'name' => $request->name,
            'updated_by' => auth('api')->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Unit updated successfully.',
            'data' => $unit,
        ]);
    }

    public function destroy($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['status' => false, 'message' => 'Unit not found.'], 404);
        }

        $unit->delete();

        return response()->json([
            'status' => true,
            'message' => 'Unit deleted successfully.',
        ]);
    }
}
