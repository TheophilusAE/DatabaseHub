<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SimpleMultiTableController extends Controller
{
    public function viewTables()
    {
        return view('simple-multi.view-tables');
    }
}
