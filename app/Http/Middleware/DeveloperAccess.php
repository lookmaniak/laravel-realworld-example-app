<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeveloperAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /*
        if (!Auth::user()->is_developer) {
            return redirect(route('dashboard_admin'))->with('error', 'Akses ke halaman tujuan tidak diizinkan!');            
        } 
        */
        return $next($request);
    }
}
