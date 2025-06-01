<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Status;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with(['user', 'category', 'brand', 'color', 'status'])->inRandomOrder()->get();
        return view("top.index", compact("items"));
    }

    public function show(Item $item)
    {
        $item = Item::with(['user', 'category', 'brand', 'color', 'status'])->findOrFail($item->id);
        return view("items.detail", [
            "item" => $item,
            "user" => $item->user,
            "category" => $item->category,
            "brand" => $item->brand,
            "color" => $item->color,
            "status" => $item->status,
        ]);
    }

}
