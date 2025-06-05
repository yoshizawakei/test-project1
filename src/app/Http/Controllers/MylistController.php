<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class MylistController extends Controller
{
    /**
     * Display the user's mylist.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(["massage" => "Unauthorized"], 401);
        }

        $likedItems = $user->likes()->with("item")->get()->map(function($like) {
            return $like->item;
        })->filter()->values();

        return response()->json($likedItems);
    }
}
