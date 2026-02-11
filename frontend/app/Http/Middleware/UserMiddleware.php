<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('authenticated') || !session()->has('user')) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = session('user');
        if (!isset($user['role']) || $user['role'] !== 'user') {
            abort(403, 'Unauthorized. User access required.');
        }

        return $next($request);
    }
}
