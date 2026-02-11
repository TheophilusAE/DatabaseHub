<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserDashboardController extends Controller
{
    public function index()
    {
        try {
            $apiBaseUrl = config('app.api_base_url', 'http://localhost:8080');
            
            // Fetch stats from backend API with timeout
            $response = Http::timeout(5)->get($apiBaseUrl . '/data', [
                'page' => 1,
                'limit' => 1
            ]);
            
            $totalRecords = $response->successful() ? ($response->json()['total'] ?? 0) : 0;

            // Fetch documents count
            $documentsResponse = Http::timeout(5)->get($apiBaseUrl . '/documents');
            $totalDocuments = $documentsResponse->successful() ? count($documentsResponse->json()) : 0;

            return view('user.dashboard', compact('totalRecords', 'totalDocuments'));
        } catch (\Exception $e) {
            \Log::error('User Dashboard Error: ' . $e->getMessage());
            return view('user.dashboard', [
                'totalRecords' => 0,
                'totalDocuments' => 0
            ]);
        }
    }
}
