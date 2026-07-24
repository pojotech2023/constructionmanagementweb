<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('name')->get();

        return view('admin.menus.unit.index', compact('units'));
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:units,name',
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        Unit::create([
            'name' => $request->name,
            'created_by' => auth('admin')->id(),
        ]);

        return redirect()->route('unit.list')->with('success', 'Unit added successfully!');
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:units,name,' . $unit->id,
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $unit->update([
            'name' => $request->name,
            'updated_by' => auth('admin')->id(),
        ]);

        return redirect()->route('unit.list')->with('success', 'Unit updated successfully!');
    }

    public function delete($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return back()->with('success', 'Unit deleted successfully!');
    }
}
