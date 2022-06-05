<?php

namespace App\Http\Controllers;

use App\Models\Prodimages;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class ProdimagesController extends Controller
{

    public function index($id)
    {
        $product = Product::find($id);
        if ($product) {
            $imgs = $product->images()->get();
            foreach ($imgs as $img) {
                $data[] = [
                    "id" => $img->id,
                    "path" => $img->path,
                    "user_id" => $img->user_id,
                    "product_id" => $img->product_id,
                ];
            }
            return response()->json(["ُimages" => $data], 200);
        }
        return response()->json(["ُmessage" => __("messages.Sorry,Product not found :(")], 404);
    }

    public function store(Request $request, $id)
    {
        $product = Product::find($id);
        if ($product) {
            if (Gate::allows('Product-Protection', $product)) {
                $request->validate([
                    'imgs' => ['array'],
                    'imgs.*' => ['image', 'nullable', 'mimes:jpg,png,jpeg,gif,svg,bmp', 'max:20480']
                ]);
                if ($request->hasFile('imgs')) {
                    foreach ($request->file('imgs') as $img) {
                        $destination_path = 'public/images/products';
                        $image = $img;
                        $randomString = Str::random(50);
                        $image_name = Auth::id() . '/' . $product->id . '/' . $randomString . $image->getClientOriginalName();
                        $path = $image->storeAs($destination_path, $image_name);
                        Prodimages::create([
                            'product_id' => $id,
                            'user_id' => Auth::id(),
                            'path' => $image_name,
                        ]);
                    }
                }
                return response()->json(["ُmessage" => "success"], 201);
            }
            return response()->json(["message" => __("messages.You are not allowed to edit this product")], 401);
        }
        return response()->json(["ُmessage" => __("messages.Sorry,Product not found :(")], 404);
    }

    public function destroy(Request $request, $id)
    {
        $product = Product::find($id);
        if ($product) {
            if (Gate::allows('Product-Protection', $product)) {
                $imgs = $request->imgs;

                for ($i = 0; $i < count($imgs); $i++) {
                    $img = Prodimages::find($imgs[$i]);
                    if ($img) {
                        $path = $img->path;
                        Storage::delete('public/images/products/' . $path);
                        $img->delete();
                    }
                }
                return response()->json(["ُmessage" => "success"], 201);
            }
            return response()->json(["message" => __("messages.You are not allowed to edit this product")], 401);
        }
        return response()->json(["ُmessage" => __("messages.Sorry,Product not found :(")], 404);
    }
}
