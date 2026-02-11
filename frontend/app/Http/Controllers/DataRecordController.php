<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataRecordController extends Controller
{
    /**
     * Display the data records dashboard
     */
    public function index()
    {
        return view('data-records.index');
    }

    /**
     * Show the form for creating a new data record
     */
    public function create()
    {
        return view('data-records.create');
    }

    /**
     * Show the form for editing a data record
     */
    public function edit($id)
    {
        return view('data-records.edit', compact('id'));
    }
}
