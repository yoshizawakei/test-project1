<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

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
