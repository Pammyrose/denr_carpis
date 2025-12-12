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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('Admin middleware triggered', [
            'user_authenticated' => Auth::check(),
            'user_id' => Auth::check() ? Auth::id() : null,
            'is_admin' => Auth::check() ? Auth::user()->is_admin : null,
            'current_url' => $request->url()
        ]);

        if (Auth::check() && Auth::user()->is_admin == 0) {
            Log::info('Redirecting non-admin user to profile', ['user_id' => Auth::id()]);
            return redirect()->route('profile');
        }

        return $next($request);
    }
}