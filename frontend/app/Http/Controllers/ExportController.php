<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExportController extends Controller
{
    /**
     * Display the export dashboard
     */
    public function index()
    {
        return view('export.index');
    }
}
