<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MultiTableController extends Controller
{
    // Multi-Table Hub - Unified Interface
    public function hub()
    {
        return view('multi-table.hub');
    }

    // Database Connections
    public function databases()
    {
        return view('multi-table.databases');
    }

    // Table Configurations
    public function tables()
    {
        return view('multi-table.tables');
    }

    // Table Joins
    public function joins()
    {
        return view('multi-table.joins');
    }

    // Import Mappings
    public function importMappings()
    {
        return view('multi-table.import-mappings');
    }

    // Export Configurations
    public function exportConfigs()
    {
        return view('multi-table.export-configs');
    }

    // Multi-Table Import
    public function import()
    {
        return view('multi-table.import');
    }

    // Multi-Table Export
    public function export()
    {
        return view('multi-table.export');
    }
}
