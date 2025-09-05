<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleRedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $role = $user->role;
            $currentPanel = filament()->getCurrentPanel()->getId();

            if ($role === 'admin' && $currentPanel !== 'admin') {
                return redirect()->route('filament.admin.pages.dashboard');
            }
            if ($role === 'teacher' && $currentPanel !== 'teacher') {
                return redirect()->route('filament.teacher.pages.dashboard');
            }
            if ($role === 'student' && $currentPanel !== 'student') {
                return redirect()->route('filament.student.pages.dashboard');
            }
        }

        return $next($request);
    }
}
