<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleRememberToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika user sudah login, lanjutkan
        if (Auth::check()) {
            return $next($request);
        }

        // Cek apakah ada remember token di cookie
        $rememberToken = $request->cookie(config('session.remember_cookie_name', 'remember_web'));
        
        if ($rememberToken) {
            // Coba login menggunakan remember token
            if (Auth::viaRemember()) {
                Log::info('User logged in via remember token', [
                    'user_id' => Auth::id(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } else {
                // Remember token tidak valid, hapus cookie
                Log::warning('Invalid remember token detected', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                // Hapus cookie remember token yang tidak valid
                cookie()->queue(cookie()->forget(config('session.remember_cookie_name', 'remember_web')));
            }
        }

        return $next($request);
    }
}