<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{ //$id = product id
    public function store($id)
    {
        if (Product::find($id)) {
            $query = Like::query();
            $query->where('product_id', $id);
            $query->where('user_id', Auth::id());
            $like = $query->get()->first();
            if ($like) {
                return response()->json([], 400);
            }
            Like::create([
                'product_id' => $id,
                'user_id' => Auth::id(),
            ]);
            return response()->json(["message" => "success"], 200);
        }
        return response()->json(["ُmessage" => __("messages.Sorry,Product not found :(")], 404);
    }

    public function destroy($id)
    {
        if (Product::find($id)) {
            $product_id = $id;
            $user_id = Auth::id();
            $query = Like::query();
            $query->where('product_id', $product_id);
            $query->where('user_id', $user_id);
            $like = $query->get()->first();
            if (!$like) {
                return response()->json([], 400);
            }
            $like->delete();
            return response()->json(["message" => "success"], 200);
        }
        return response()->json(["ُmessage" => __("messages.Sorry,Product not found :(")], 404);
    }
}
