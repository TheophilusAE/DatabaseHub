<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentController extends Controller
{
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
}
