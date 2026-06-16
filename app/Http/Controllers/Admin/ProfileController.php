<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        return view('admin.menus.profile.profile_update', compact('user'));
    }

    public function update(Request $request)
    {
        //dd($request->all());
        $user = User::findOrFail($request->user_id);

        $validate = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name'  => 'nullable|string|max:255',
            'mobile_no' => 'nullable|digits:10',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        // Update basic fields
        $user->name = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;
        $user->mobile_no = $request->mobile_no ?? $user->mobile_no;

        // Update password only if entered
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $userImg = $user->image;
        if ($request->hasFile('image')) {
            if (!empty($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $userImg = $request->file('image')->store('profile', 'public');
            $user->image = $userImg;
        }

        $user->updated_by = auth('admin')->id();
        $user->save();

        return redirect()->back()->with('success', 'Your Profile updated successfully!');
    }
}
