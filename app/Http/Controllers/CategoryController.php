<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::all(), 200);
    }
    public function store(Request $request)
    {
        if (Gate::allows('CUD-Categories')) {
            $request->validate([
                'name' => ['required', 'min:2', 'max:255', 'unique:categories,name'],
                'image' => ['image', 'mimes:jpg,png,jpeg,svg,bmp', 'max:4096'], //4MB
                'description' => ['string']
            ]);
            $input = $request->all();
            if ($request->hasFile('image')) {
                $destination_path = 'public/images/categories';
                $image = $request->file('image');
                $randomString = Str::random(50);
                $image_name = $randomString . $image->getClientOriginalName();
                $image->storeAs($destination_path, $image_name);
                $input['img_url'] = $image_name;
            }
            $category = Category::create($input);
            return response()->json(["message" => "success"], 201);
        }
        return response()->json(["message" => __("messages.You are not allowed to this")], 401);
    }

    public function update(Request $request, $id)
    {
        if (Gate::allows('CUD-Categories')) {
            $category = Category::find($id);
            if ($category) {
                $request->validate([
                    'name' => ['min:2', 'max:255', Rule::unique('categories', 'name')->ignore($id)],
                    'image' => ['image', 'mimes:jpg,png,jpeg,svg,bmp', 'max:4096'], //4MB
                    'description' => ['string']
                ]);
                $input = $request->only('name', 'image', 'description');
                if ($request->hasFile('image')) {
                    if ($category->img_url != "pxcLtWJY7ahgoCE5toU8EtJ0OvYnJxPuioAoyXzhUj71k8DA0kdefaultcategoryimg.png") {
                        Storage::delete('public/images/categories/' . $category->img_url);
                    }
                    $destination_path = 'public/images/categories';
                    $image = $request->file('image');
                    $randomString = Str::random(50);
                    $image_name = $randomString . $image->getClientOriginalName();
                    $image->storeAs($destination_path, $image_name);
                    $category->update([
                        'img_url' => $image_name
                    ]);
                }
                if ($request->name) {
                    $category->update([
                        'name' => $request->name
                    ]);
                }
                if ($request->description) {
                    $category->update([
                        'description' => $request->description
                    ]);
                }
                return response()->json(["message" => "success"], 200);
            }
            return response()->json(["message" => __("messages.Category not found")], 404);
        }
        return response()->json(["message" => __("messages.You are not allowed to this")], 401);
    }

    public function show($id)
    {
        if (!Category::find($id)) {
            return response()->json(["message" => __("messages.Category not found")], 404);
        }
        $productQuery = Product::query();
        if ($id) {
            $productQuery->where('category_id', $id);
        }
        $products = $productQuery->paginate(20);
        if (!$products->isEmpty() && Product::first()) {
            foreach ($products as $product) {
                $max_discount = "null";
                $current_price = $product->price;
                if ($product->sales()->get()->first()) {
                    $current_price = $product->price - ($product->price * (float)$product->sales()->get()->first()->discount / 100);
                    $max_discount = $product->sales()->get()->first()->discount;
                }
                $data[] = [
                    "id" => $product->id,
                    "name" => $product->name,
                    "views" => $product->views,
                    "img" => $product->img_url,
                    "LikesNum" => $product->likes()->count(),
                    "current_price" => $current_price,
                    "max_discount" => $max_discount,
                ];
            }
            return response()->json(["products" => $data], 200);
        }
        return response()->json(["products" => []], 200);
    }

    public function destroy($id)
    {
        if (Gate::allows('CUD-Categories')) {
            $category = Category::find($id);
            if ($category) {
                if ($category->img_url != "pxcLtWJY7ahgoCE5toU8EtJ0OvYnJxPuioAoyXzhUj71k8DA0kdefaultcategoryimg.png") {
                    Storage::delete('public/images/categories/' . $category->img_url);
                }
                $category->delete();
                return response()->json(["message" => __('messages.Category has been deleted successfully')], 200);
            }
            return response()->json(["message" => __("messages.Category not found")], 404);
        }
        return response()->json(["message" => __("messages.You are not allowed to this")], 401);
    }

    public function search(Request $request)
    {
        $lan = $request->header("lan");
        $name = strtolower($request->header('name'));
        $categoryQuery = Category::query();
        $categoryQuery->where('name', 'like', '%' . $name . '%');
        $category = $categoryQuery->get();
        if ($category->isEmpty()) {
            if ($lan == "ar") {
                return response()->json(["message" => $name . "نأسف , لم يتم العثور على فئة ال"]);
            }
            return response()->json(["message" => 'Sorry, the category ' . $request->name . ' does not exist !']);
        }
        return response()->json(["categories" => $category], 200);
    }
    public function searchsug(Request $request)
    {
        $name = $request->header('name');
        $data = Category::query()->where('name', 'like', '%' . strtolower($name) . '%')->inRandomOrder()->limit(4)->get("name");
        return response()->json(["data" => $data], 200);
    }
    public function catList()
    {
        return response()->json(["data" => Category::query()->get(["id", "name"])], 200);
    }
}
