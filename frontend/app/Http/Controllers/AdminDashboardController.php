<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminDashboardController extends Controller
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

            // Fetch category counts
            $categories = ['electronics', 'furniture', 'clothing', 'books'];
            $categoryData = [];
            foreach ($categories as $category) {
                $categoryResponse = Http::timeout(5)->get($apiBaseUrl . "/data/category/{$category}", [
                    'page' => 1,
                    'limit' => 1
                ]);
                $categoryData[$category] = $categoryResponse->successful() ? ($categoryResponse->json()['total'] ?? 0) : 0;
            }

            return view('admin.dashboard', compact('totalRecords', 'totalDocuments', 'categoryData'));
        } catch (\Exception $e) {
            \Log::error('Admin Dashboard Error: ' . $e->getMessage());
            return view('admin.dashboard', [
                'totalRecords' => 0,
                'totalDocuments' => 0,
                'categoryData' => [
                    'electronics' => 0,
                    'furniture' => 0,
                    'clothing' => 0,
                    'books' => 0
                ]
            ]);
        }
    }
}
