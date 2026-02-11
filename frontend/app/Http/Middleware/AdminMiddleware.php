<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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
        if (!isset($user['role']) || $user['role'] !== 'admin') {
            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}
