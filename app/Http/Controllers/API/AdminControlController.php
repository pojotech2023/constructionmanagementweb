<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminControlController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Admin control settings fetched successfully.',
            'data' => [
                'menu_groups' => Setting::getMenuGroups(),
                'visibility' => Setting::getMenuVisibility(),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $menu = $request->input('menu', []);

        Setting::setMenuVisibility($menu);

        return response()->json([
            'status' => true,
            'message' => 'Sidebar menu settings updated successfully.',
            'data' => [
                'visibility' => Setting::getMenuVisibility(),
            ],
        ]);
    }
}
