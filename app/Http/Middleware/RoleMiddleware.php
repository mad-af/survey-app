<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Convert role enum to string for comparison
        $userRole = $user->role instanceof UserRole ? $user->role->value : $user->role;
        
        // Check if user has any of the required roles
        if (!in_array($userRole, $roles)) {
            // If user is surveyor trying to access admin-only features, redirect to dashboard
            if ($userRole === UserRole::SURVEYOR->value) {
                return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
            
            // For other cases, return 403
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}