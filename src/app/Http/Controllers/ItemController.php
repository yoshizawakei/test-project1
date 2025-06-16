<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with(['user', 'categories', 'brand'])->inRandomOrder()->get();
        return view("top.index", compact("items"));
    }

    public function show(Item $item)
    {
        $item = Item::with(['user', 'categories', 'brand'])->findOrFail($item->id);
        return view("items.detail", [
            "item" => $item,
            "user" => $item->user,
            "brand" => $item->brand,
        ]);
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        return view("items.sell", compact("categories", "brands"));
    }

    public function store(Request $request)
    {
        $imagePath = null;
        if ($request->hasFile("image")) {
            $imagePath = $request->file("image")->store("public/items");
            $imagePath = str_replace("public/", "storage/", $imagePath);
        }

        $userId = Auth::id();

        $item = Item::create([
            "item_name" => $request->item_name,
            "price" => $request->price,
            "description" => $request->description,
            "image_path" => $imagePath,
            "condition" => $request->condition,
            "user_id" => $userId,
            "brand_id" => $request->brand_id,
            // "sold_at" => null,
            // "buyer_id" => null,
        ]);

        $item->categories()->sync($request->category_ids);

        return redirect()->route("top.index")->with("success", "商品を出品しました。");
    }

    public function edit(Item $item)
    {
        if (Auth::id() !== $item->user_id) {
            abort(403, "Unauthorized action.");
        }

        $categories = Category::all();
        $brands = Brand::all();
        $conditions = [
            "良好",
            "目立った傷や汚れなし",
            "やや傷や汚れあり",
            "状態が悪い",
        ];

        $item->load("categories");

        return view("items.edit", compact("item", "categories", "brands", "conditions"));
    }

    public function update(Request $request, Item $item)
    {
        if (Auth::id() !== $item->user_id) {
            abort(403, "Unauthorized action.");
        }

        $imagePath = $item->image_path;

        if ($request->hasFile("image")) {
            if ($item->image_path) {
                Storage::delete(str_replace("storage/", "public/", $item->image_path));
            }
            $imagePath = $request->file("image")->store("public/items");
            $imagePath = str_replace("public/", "storage/", $imagePath);
        }

        $item->update([
            "item_name" => $request->item_name,
            "price" => $request->price,
            "description" => $request->description,
            "image_path" => $imagePath,
            "condition" => $request->condition,
            "brand_id" => $request->brand_id,
        ]);

        $item->categories()->sync($request->category_ids);

        return redirect()->route("items.detail",$item)->with("success", "商品情報を更新しました。");
    }

    public function destroy(Item $item)
    {
        if (Auth::id() !== $item->user_id) {
            abort(403, "Unauthorized action.");
        }

        if ($item->image_path) {
            Storage::delete(str_replace("storage/", "public/", $item->image_path));
        }

        $item->delete();

        return redirect()->route("top.index")->with("success", "商品を削除しました。");
    }

    public function purchase(Item $item)
    {
        if (Auth::id() === $item->user_id) {
            abort(403, "You cannot purchase your own item.");
        }
        
        return view("items.purchase", compact("item"));
    }

}
