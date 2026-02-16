<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SimpleMultiTableController extends Controller
{
    public function viewTables()
    {
        return view('simple-multi.view-tables');
    }

    public function multiUpload()
    {
        return view('simple-multi.multi-upload');
    }

    public function selectiveExport()
    {
        return view('simple-multi.selective-export');
    }
}
