<?php

namespace App\Http\Controllers;

class DocumentCategoryController extends Controller
{
    /**
     * Display the admin category management page.
     */
    public function index()
    {
        return view('admin.document-categories.index');
    }
}
