<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DocumentController extends Controller
{
    private string $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = config('app.api_base_url', 'http://localhost:8080');
    }

    /**
     * Display the documents dashboard
     */
    public function index()
    {
        return view('documents.index');
    }

    /**
     * Show the form for uploading a new document
     */
    public function create()
    {
        return view('documents.create');
    }

    /**
     * Delete a document via backend API (proxy through Laravel to avoid browser CORS issues)
     */
    public function destroy($id)
    {
        try {
            $response = Http::timeout(10)->delete("{$this->apiBaseUrl}/documents/{$id}");

            if ($response->successful()) {
                return response()->json([
                    'message' => 'Document deleted successfully',
                ], 200);
            }

            $payload = $response->json();
            return response()->json([
                'error' => $payload['error'] ?? 'Failed to delete document',
            ], $response->status());
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Unable to delete document at this time',
            ], 500);
        }
    }
}
