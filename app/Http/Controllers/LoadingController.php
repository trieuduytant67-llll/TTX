<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoadingController extends Controller
{
    public function index(Request $request)
    {
        $target = $request->query('target', '/');
        return view('loading', compact('target'));
    }
}