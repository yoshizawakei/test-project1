<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        return view("top.index");
    }

    public function show()
    {
        return view("items.detail");
    }

}
