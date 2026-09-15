<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TermsCondition;
use App\Models\Unit;

class SettingsController extends Controller
{
    public function index()
    {
        $isAdmin = session('role_name') == 'Admin';

        $units = $isAdmin ? Unit::orderBy('name')->get() : collect();
        $termsConditions = TermsCondition::orderBy('title')->get();

        return view('admin.menus.settings.index', compact('units', 'termsConditions', 'isAdmin'));
    }
}
