<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataExchangeController extends Controller
{
    public function index()
    {
        return view('data-exchange.index');
    }
}
