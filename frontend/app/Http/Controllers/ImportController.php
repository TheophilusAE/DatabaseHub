<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImportController extends Controller
{
    /**
     * Display the import dashboard
     */
    public function index()
    {
        return view('import.index');
    }

    /**
     * Show the import history
     */
    public function history()
    {
        return view('import.history');
    }
}
