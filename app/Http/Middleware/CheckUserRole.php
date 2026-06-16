<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {

        // if (!Auth::guard('admin')->check()) {
        //     return redirect()->route('admin.login')->with('error', 'You must be logged in.');
        // }
        $role = session('role_name');
    
        if (!$role || !in_array($role, $roles)) {
            abort(403, 'Unauthorized action.');
        }
    
        return $next($request);
    }
    
    

}
