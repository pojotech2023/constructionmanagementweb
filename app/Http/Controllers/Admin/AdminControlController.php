<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminControlController extends Controller
{
    public function index()
    {
        $menuGroups = Setting::getMenuGroups();
        $visibility = Setting::getMenuVisibility();

        return view('admin.menus.admin_control.index', compact('menuGroups', 'visibility'));
    }

    public function update(Request $request)
    {
        $menu = $request->input('menu', []);

        Setting::setMenuVisibility($menu);

        return redirect()->route('admin.control.index')->with('success', 'Sidebar menu settings updated successfully.');
    }
}
