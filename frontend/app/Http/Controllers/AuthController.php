<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (session()->has('authenticated')) {
            $user = session('user');
            return redirect()->route($user['role'] === 'admin' ? 'admin.dashboard' : 'user.dashboard');
        }
        return view('auth.login');
    }

    /**
     * Show register form
     */
    public function showRegister()
    {
        if (session()->has('authenticated')) {
            $user = session('user');
            return redirect()->route($user['role'] === 'admin' ? 'admin.dashboard' : 'user.dashboard');
        }
        return view('auth.register');
    }

    /**
     * Handle login request - calls Go API
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $response = Http::post(config('app.api_base_url') . '/auth/login', [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Store user data in session
                session([
                    'authenticated' => true,
                    'user' => $data['user'],
                ]);

                $user = $data['user'];
                
                if ($user['role'] === 'admin') {
                    return redirect()->intended(route('admin.dashboard'));
                }
                
                return redirect()->intended(route('user.dashboard'));
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');

        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Unable to connect to authentication service.',
            ])->onlyInput('email');
        }
    }

    /**
     * Handle register request - calls Go API
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        try {
            $response = Http::post(config('app.api_base_url') . '/auth/register', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Store user data in session
                session([
                    'authenticated' => true,
                    'user' => $data['user'],
                ]);

                return redirect()->route('user.dashboard');
            }

            // Handle validation errors from API
            if ($response->status() === 422 || $response->status() === 400) {
                $error = $response->json();
                return back()->withErrors([
                    'email' => $error['message'] ?? 'Registration failed.',
                ])->withInput();
            }

            return back()->withErrors([
                'email' => 'Registration failed. Please try again.',
            ])->withInput();

        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Unable to connect to registration service.',
            ])->withInput();
        }
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        // Clear session
        session()->forget(['authenticated', 'user']);
        session()->flush();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}