<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    private $apiBaseUrl;

    public function __construct()
    {
        // Go backend API URL
        $this->apiBaseUrl = env('API_BASE_URL', 'http://localhost:8080');
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        try {
            $response = Http::get("{$this->apiBaseUrl}/users", [
                'page' => $request->get('page', 1),
                'per_page' => 10,
                'search' => $request->get('search', ''),
                'role' => $request->get('role', 'all'),
            ]);

            $statsResponse = Http::get("{$this->apiBaseUrl}/users/stats");

            if ($response->successful()) {
                $data = $response->json();
                $stats = $statsResponse->successful() ? $statsResponse->json('data') : ['total' => 0, 'admins' => 0, 'users' => 0];
                
                // Create pagination object for Laravel views
                $users = collect($data['data']);
                $pagination = new \Illuminate\Pagination\LengthAwarePaginator(
                    $users,
                    $data['total'],
                    $data['per_page'],
                    $data['page'],
                    ['path' => $request->url(), 'query' => $request->query()]
                );

                return view('admin.users.index', [
                    'users' => $pagination,
                    'stats' => $stats,
                ]);
            }

            return redirect()->back()->with('error', 'Failed to load users');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to connect to server');
        }
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user via API
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:admin,user'],
        ]);

        try {
            $response = Http::post("{$this->apiBaseUrl}/users", [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
            ]);

            if ($response->successful() && $response->json('success')) {
                return redirect()->route('admin.users.index')
                    ->with('success', 'User created successfully!');
            }

            $error = $response->json('error') ?? 'Failed to create user';
            return back()->withErrors(['email' => $error])->withInput();

        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Unable to connect to server'])->withInput();
        }
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        try {
            $response = Http::get("{$this->apiBaseUrl}/users/{$id}");

            if ($response->successful() && $response->json('success')) {
                $user = (object) $response->json('data');
                return view('admin.users.edit', compact('user'));
            }

            return redirect()->route('admin.users.index')
                ->with('error', 'User not found');

        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Unable to connect to server');
        }
    }

    /**
     * Update the specified user via API
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'in:admin,user'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        // Add password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', 'min:8'],
            ]);
            $data['password'] = $request->password;
        }

        try {
            $response = Http::put("{$this->apiBaseUrl}/users/{$id}", $data);

            if ($response->successful() && $response->json('success')) {
                // Update session if editing own account
                if (session('user.id') == $id) {
                    $userData = $response->json('data');
                    session(['user' => $userData]);
                }

                return redirect()->route('admin.users.index')
                    ->with('success', 'User updated successfully!');
            }

            $error = $response->json('error') ?? 'Failed to update user';
            return back()->withErrors(['email' => $error])->withInput();

        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Unable to connect to server'])->withInput();
        }
    }

    /**
     * Remove the specified user via API
     */
    public function destroy($id)
    {
        // Prevent deleting own account
        if (session('user.id') == $id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account!');
        }

        try {
            $response = Http::delete("{$this->apiBaseUrl}/users/{$id}");

            if ($response->successful() && $response->json('success')) {
                return redirect()->route('admin.users.index')
                    ->with('success', 'User deleted successfully!');
            }

            return redirect()->route('admin.users.index')
                ->with('error', 'Failed to delete user');

        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Unable to connect to server');
        }
    }

    /**
     * Bulk delete users
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
        ]);

        // Remove current user ID from the list
        $userIds = array_filter($validated['user_ids'], function($id) {
            return $id != session('user.id');
        });

        try {
            $deleted = 0;
            foreach ($userIds as $id) {
                $response = Http::delete("{$this->apiBaseUrl}/users/{$id}");
                if ($response->successful()) {
                    $deleted++;
                }
            }

            return redirect()->route('admin.users.index')
                ->with('success', "{$deleted} user(s) deleted successfully!");

        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Unable to connect to server');
        }
    }
}
