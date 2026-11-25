<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, int ...$allowedRoles)
    {
        $allowedRoles = $allowedRoles ?: [1, 2];
        $role = (int) Auth::user()?->is_admin;

        if (! Auth::check() || ! in_array($role, $allowedRoles, true)) {
            Log::warning('Redirecting non-admin user to profile', ['user_id' => Auth::id()]);

            return redirect()->route('profile');
        }

        return $next($request);
    }
}
