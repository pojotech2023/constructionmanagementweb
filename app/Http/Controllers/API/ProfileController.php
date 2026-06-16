<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
{
    $user = Auth::user();

    // Hide sensitive fields
    unset($user->password);
   

    return response()->json([
        'response code' => 200,
        'data' => $user,
        'status' => true,
        'message' => 'Auth User fetched successfully!',
    ]);
}

    public function update(Request $request)
{
    $validate = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'name'    => 'nullable|string',
        'email'   => 'nullable|email|unique:users,email,' . $request->user_id,
        'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp'
    ]);

    if ($validate->fails()) {
        return redirect()->back()->withErrors($validate)->withInput();
    }

    $user = User::findOrFail($request->user_id);

    $data = [
        'name'       => $request->name,
        'email'      => $request->email,
        'updated_by' => auth('api')->id(),
    ];

    // ✔️ Update only if image is sent
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('profile', 'public');
        $data['image'] = $imagePath;  // add image only when exists
    }

    $user->update($data);

    unset($user->password);

    return response()->json([
        'response code' => 200,
        'data' => $user,
        'status' => true,
        'message' => 'Auth User updated successfully!',
    ]);
}

}
