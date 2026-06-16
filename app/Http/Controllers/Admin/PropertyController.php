<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
  public function index()
  {
    $properties = Property::orderBy('id', 'desc')->get();
    return view('admin.menus.property.property_management', compact('properties'));
  }

  public function getPropertyForm()
  {
    return view('admin.menus.property.add_property');
  }

    public function store(Request $request)
    {
    $validate = Validator::make($request->all(), [
      'name' => 'required',
      'location' => 'required',
      'type' => 'required',
      'amount' => 'required|numeric',
      'remarks' => 'required',
      'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
    ]);

    if ($validate->fails()) {
      if ($request->expectsJson() || $request->ajax()) {
        return response()->json([
          'status' => 'error',
          'errors' => $validate->errors(),
        ], 422);
      }

      return redirect()->back()->withErrors($validate)->withInput();
    }
    // Upload Image
    $imagePath = null;
    if ($request->hasFile('image')) {
      $imagePath = $request->file('image')->store('properties', 'public');
    }

    $property = Property::create([
      'name' => $request->name,
      'location' => $request->location,
      'type' => $request->type,
      'amount' => $request->amount,
      'remarks' => $request->remarks,
      'image' => $imagePath,
       'created_by'  => auth('admin')->id(),
    ]);

    $message = "* Pojo Infra360 *\n" .
      "New Property Added\n" .
      "Name: {$property->name}\n" .
      "Location: {$property->location}\n" .
      "Type: {$property->type}\n" .
      "Amount: {$property->amount}\n" .
      "Remarks: {$property->remarks}";

    if ($property->image) {
      $imgUrl = asset('storage/' . $property->image);
      $message .= "\n\n *Image:* {$imgUrl}";
    }

    $url = "https://wa.me/?text=" . urlencode($message);

    return response()->json([
      'status' => 'success',
      'whatsapp_url' => $url,
      'message' => 'Property created successfully.',
      'data' => $property,
    ]);
  }
}
