<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Site;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function adminlogin(Request $request)
    {
        //dd($request->all());
        $validate = Validator::make($request->all(), [
            'mobile_no' => 'required|numeric|digits:10',
            'password'  => 'required|min:6'
        ]);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        $user = User::where('mobile_no', $request->mobile_no)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            session(['role_name' => $user->roles->first()->role_name]);
            Auth::guard('admin')->login($user);

            // site summary details
            $totalSites = Site::count();
            $activeSites = Site::where('is_inactive', 0)->count();
            $siteLimit = Setting::getCurrentPlan()['site_limit'];
            $remainingSites = max(0, $siteLimit - $activeSites);

            session([
                'dashboard_total_sites' => $totalSites,
                'dashboard_active_sites' => $activeSites,
                'dashboard_remaining_sites' => $remainingSites,
                'dashboard_site_limit' => $siteLimit,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Login successful',
                    'total_sites' => $totalSites,
                    'active_sites' => $activeSites,
                    'remaining_sites' => $remainingSites,
                    'site_limit' => $siteLimit,
                ]);
            }

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['error' => 'Invalid credentials']);
    }
 
    public function adminLogout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }





}
