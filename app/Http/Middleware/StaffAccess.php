<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffAccess
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
        
        switch (Auth::user()->level) {
            case 0:
                return $next($request);
                break;
            case 3:
                return $next($request);
                break;
            default:
                return redirect(route('dashboard_client'))->with('error', 'Akses ke halaman tujuan tidak diizinkan!');
                break;
        }
    }
}
