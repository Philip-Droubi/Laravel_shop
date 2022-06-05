<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Like;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use function PHPUnit\Framework\isNull;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()->inRandomOrder()->paginate(20); //return the random 20 product in the products table
        //pagination here should be created by frontend as back can't use paginate() on $data array
        //EX :http://127.0.0.1:8000/api/products/?page=1 first (20) product
        //EX :http://127.0.0.1:8000/api/products/?page=2 next (20) product
        if (!$products->isEmpty()) {
            foreach ($products as $product) {
                $data[] = ProductController::cp($product);
            }
            return response()->json(["products" => $data], 200);
        }
        if ($products->isEmpty() && !Product::first())
            return response()->json(['message' => __("messages.No one added any product to the app yet")], 200);
        if ($products->isEmpty() && Product::first())
            return response()->json([], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'min:2', 'max:255'],
            'price' => ['required', 'min:1', "numeric"],
            'category_id' => ['required', 'numeric', 'min:1', 'exists:categories,id'],
            'quantity' => ['required', 'numeric', 'min:1'],
            'description' => ['string', 'max:1000', 'nullable'],
            'exp_date' => ['date', 'string', 'nullable'],
            'image' => ['image', 'nullable', 'mimes:jpg,png,jpeg,gif,svg,bmp', 'max:4096'] //4MB
        ]);
        $input = $request->all();
        $input["user_id"] = Auth::id();
        if ($request->exp_date) {
            $input['exp_date'] = Carbon::createFromFormat('Y-m-d', $request->exp_date)->format('Y-m-d');
            if ($input['exp_date'] < now())
                return response()->json(["ُmessage" => __("messages.Expire date should be > ") . now()->format('Y-m-d')], 400);
        }
        $product = Product::create($input);
        if ($request->hasFile('image')) {
            $destination_path = 'public/images/products';
            $image = $request->file('image');
            $randomString = Str::random(50);
            $image_name = $input["user_id"] . '/' . $product->id . '/' . $randomString . $image->getClientOriginalName();
            $path = $image->storeAs($destination_path, $image_name);
            DB::table('products')->where('id', $product->id)->update(['img_url' => $image_name]);
        }
        $message =  $product->name . __("messages.has been added successfuly");
        $data['product'] = [
            "product_id" => $product->id,
            "message" => $message
        ];
        return response()->json($data, 201);
    }

    public function show($id)
    {
        $product = Product::find($id);
        if (is_null($product))
            return response()->json(["ُmessage" => __("messages.Sorry,Product not found :(")], 404);
        DB::table('products')->where('id', $id)->increment('views'); //add 1 to views
        $max_discount = "null";
        $current_price = $product->price;
        if ($product->sales()->get()->first()) {
            $current_price = $product->price - ($product->price * (float)$product->sales()->get()->first()->discount / 100);
            $max_discount = $product->sales()->get()->first()->discount;
        }
        $is_liked = false;
        $query = Like::query();
        $query->where('product_id', $id);
        $query->where('user_id', Auth::id());
        $like = $query->get()->first();
        if ($like) {
            $is_liked = true;
        }

        $data = [
            "id" => $product->id,
            "name" => $product->name,
            "views" => $product->views + 1,
            "img" => $product->img_url,
            "price" => $product->price,
            "description" => $product->description,
            "quantity" => $product->quantity,
            "exp_date" => (string)$product->exp_date,
            "created_at" => $product->created_at->format("Y-m-d H-i-s"),
            "updated_at" => $product->updated_at->format("Y-m-d H-i-s"),
            "facebook_url" => (string)$product->user->facebook_url,
            "whatsapp_url" => (string)$product->user->whatsapp_url,
            "phone_number" => (string)$product->user->phone_number,
            "category_name" => Category::find($product->category_id)->name,
            "LikesNum" => $product->likes()->count(),
            "CommentsNum" => $product->comments()->count(),
            "imagesNum" => $product->images()->count(),
            "current_price" => $current_price,
            "max_discount" => $max_discount,
            "is_liked" => $is_liked,
            "user_id" => $product->user_id,
            "user_name" => $product->user->name,
            "user_img" => $product->user->prof_img_url,
        ];

        return response()->json($data, 200);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if ($product) {
            if (Gate::allows('Product-Protection', $product)) {
                $request->validate([
                    'name' => ['min:2', 'max:255', 'nullable'],
                    'price' => ['min:1', 'numeric', 'nullable'],
                    'description' => ['string', 'max:1000', 'nullable'],
                    'category_id' => ['numeric', 'min:1', 'nullable'],
                    'quantity' => ['numeric', 'min:1', 'nullable'],
                    'image' => ['image', 'nullable', 'mimes:jpg,png,jpeg,gif,svg,bmp', 'max:4096']
                ]);
                if ($request->hasFile('image')) {
                    $uid = Auth::id();
                    if ($product->img_url != "pxcLtWJY7ahgoCE5toU8EtJ0OvYnJxPuioAoyXzhUj71k8DA0kdefaultcategoryimg.png") {
                        Storage::delete('public/images/products/' . $product->img_url);
                    }
                    $destination_path = 'public/images/products'; //create img and store it
                    $image = $request->file('image');
                    $randomString = Str::random(50);
                    $image_name = $uid . '/' . $product->id . '/' . $randomString . $image->getClientOriginalName(); //random + img name
                    $path = $image->storeAs($destination_path, $image_name);
                    $product->update(['img_url' => $image_name]);
                }
                if ($request->name) {
                    $product->update([
                        'name' => $request->name
                    ]);
                }
                if ($request->price) {
                    $product->update([
                        'price' => $request->price
                    ]);
                }
                if ($request->category_id) {
                    $product->update([
                        'category_id' => $request->category_id
                    ]);
                }
                if ($request->quantity) {
                    $product->update([
                        'quantity' => $request->quantity
                    ]);
                }
                if ($request->description) {
                    $product->update([
                        'description' => $request->description
                    ]);
                }
                $message =  $product->name . __("messages.has been updated successfuly");
                $data = [
                    "product_id" => $product->id,
                    "message" => $message
                ];

                return response()->json($data, 200);
            }

            return response()->json(["message" => __("messages.You are not allowed to edit this product")], 401);
        }
        return response()->json(["message" => __("messages.The product you are trying to edit is not exists")], 404);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if ($product) {
            if (Gate::allows('Product-Protection', $product)) {
                if ($product->img_url != "pxcLtWJY7ahgoCE5toU8EtJ0OvYnJxPuioAoyXzhUj71k8DA0kdefaultcategoryimg.png") {
                    Storage::deleteDirectory('public/images/products/' . Auth::id() . '/' . $id);
                }
                $product->delete();
                return response()->json(["message" => $product->name . __("messages.has been deleted successfuly")], 200);
            }
            return response()->json(["message" => __("messages.You are not allowed to edit this product")], 401);
        }
        return response()->json(["message" => __("messages.The product you are trying to delete is not exists or has already been deleted")], 404);
    }

    public function search(Request $request)
    {
        $name = $request->header('name');
        $category_id = $request->header('category_id');
        $date_from = $request->header('date_from');
        $date_to = $request->header('date_to');

        $productQuery = Product::query();
        if ($name) {
            $productQuery->where('name', 'like', '%' . strtolower($name) . '%');
        }

        if ($category_id) {
            $productQuery->where('category_id', $category_id);
        }
        if ($date_from) {
            $Dfrom = Carbon::createFromFormat('Y-m-d', $date_from)->format('Y-m-d');;
            $productQuery->where('exp_date', '>=', $Dfrom);
        }
        if ($date_to) {
            $Dto = Carbon::createFromFormat('Y-m-d', $date_to)->format('Y-m-d');
            $productQuery->where('exp_date', '<=', $Dto);
        }
        $products = $productQuery->latest()->paginate(20);
        $data = [];
        foreach ($products as $product) {
            $data[] = ProductController::cp($product);
        }
        return response()->json(["data" => $data], 200);
    }

    public function searchsug(Request $request)
    {
        $name = $request->header('name');
        $data = Product::query()->where('name', 'like', '%' . strtolower($name) . '%')->inRandomOrder()->limit(7)->get("name");
        return response()->json(["data" => $data], 200);
    }
    private static function cp($product)
    {
        $max_discount = "null";
        $current_price = $product->price;
        if ($product->sales()->get()->first()) {
            $current_price = $product->price - ($product->price * (float)$product->sales()->get()->first()->discount / 100);
            $max_discount = $product->sales()->get()->first()->discount;
        }
        $data = [
            "id" => $product->id,
            "name" => $product->name,
            "views" => $product->views,
            "img" => $product->img_url,
            "category_name" => Category::find($product->category_id)->name,
            "LikesNum" => $product->likes()->count(),
            "current_price" => $current_price,
            "max_discount" => $max_discount,
        ];
        return $data;
    }

    public function sort(Request $request)
    {
        if (Product::first()) {
            $sort = $request->header("sort");
            if ($sort == "newest") {
                $products = Product::query()->orderBy('created_at', 'desc')->paginate(16);
                foreach ($products as $product) {
                    $data[] = ProductController::cp($product);
                }
                return response()->json($data, 200);
            }
            if ($sort == "oldest") {
                $products = Product::query()->orderBy('created_at', 'asc')->paginate(16);
                foreach ($products as $product) {
                    $data[] = ProductController::cp($product);
                }
                return response()->json($data, 200);
            }
            if ($sort == "most_views") {
                $products = Product::query()->orderBy('views', 'desc')->paginate(16);
                foreach ($products as $product) {
                    $data[] = ProductController::cp($product);
                }
                return response()->json($data, 200);
            }
            if ($sort == "least_views") {
                $products = Product::query()->orderBy('views', 'asc')->paginate(16);
                foreach ($products as $product) {
                    $data[] = ProductController::cp($product);
                }
                return response()->json($data, 200);
            }
            if ($sort == "name_desc") { //تنازلي حسب الاسم
                $products = Product::query()->orderBy('name', 'desc')->paginate(16);
                foreach ($products as $product) {
                    $data[] = ProductController::cp($product);
                }
                return response()->json($data, 200);
            }
            if ($sort == "name_asc") { //تصاعدي حسب الاسم
                $products = Product::query()->orderBy('name', 'asc')->paginate(16);
                foreach ($products as $product) {
                    $data[] = ProductController::cp($product);
                }
                return response()->json($data, 200);
            }
            if ($sort == "price_desc") { //تنازلي حسب السعر
                $products = Product::query()->orderBy('price', 'desc')->paginate(16);
                foreach ($products as $product) {
                    $data[] = ProductController::cp($product);
                }
                return response()->json($data, 200);
            }
            if ($sort == "price_asc") { //تصاعدي حسب السعر
                $products = Product::query()->orderBy('price', 'asc')->paginate(16);
                foreach ($products as $product) {
                    $data[] = ProductController::cp($product);
                }
                return response()->json($data, 200);
            }
            if ($sort == "like_desc") { //تنازلي حسب الإعجاب
                $products = Product::withCount('likes')->orderBy('likes_count', 'desc')->paginate(16);
                foreach ($products as $product) {
                    $data[] = ProductController::cp($product);
                }
                return response()->json($data, 200);
            }
            if ($sort == "like_asc") { //تصاعدي حسب الإعجاب
                $products = Product::withCount('likes')->orderBy('likes_count', 'asc')->paginate(16);
                foreach ($products as $product) {
                    $data[] = ProductController::cp($product);
                }
                return response()->json($data, 200);
            }
        }
    }

    //need check Kawser
    public function getHomeProducts()
    {
        $item = array();
        $item['MostViewedProds'] = ProductController::getMostViewedProds();
        $item['MostLikedProds'] = ProductController::getMostLikedProds();
        $item['NewestProds'] = ProductController::getNewestProds();
        $item['RandomProds'] = ProductController::getRandomProds();
        return response()->json($item);
    }
    private static function getMostViewedProds()
    {
        return DB::select('select prods.id as product_id, prods.name as product_name,currentPrice, prods.img_url, prods.description as product_description,prods.price as price, views as product_views, cats.name as category_name,cats.id as category_id from categories as cats ,( select id as id2, ( price - (case when CURDATE() >= sale_date3 AND UNIX_TIMESTAMP(sale_date3 ) != 0 then price3 when CURDATE() >= sale_date2 AND UNIX_TIMESTAMP(sale_date2 ) != 0 then price2 when CURDATE() >= sale_date1 AND UNIX_TIMESTAMP(sale_date1 ) != 0 then  price1 else 0 end)*0.01*price) As currentPrice  from products )  as salesManagement JOIN products as prods ON salesManagement.id2=prods.id  where category_id = cats.id order by product_views desc LIMIT 10');
    }
    private static function getMostLikedProds()
    {
        return DB::select('select count(likes.id) as cnt,product_id, prods.name as product_name,currentPrice,prods.img_url , prods.description as product_description, cats.name as category_name from likes,categories as cats ,( select id as id2, ( price - (case when CURDATE() >= sale_date3 AND UNIX_TIMESTAMP(sale_date3 ) != 0 then price3 when CURDATE() >= sale_date2 AND UNIX_TIMESTAMP(sale_date2 ) != 0 then price2 when CURDATE() >= sale_date1 AND UNIX_TIMESTAMP(sale_date1 ) != 0 then  price1 else 0 end)*0.01*price) As currentPrice  from products ) as salesManagement JOIN products as prods ON salesManagement.id2=prods.id  where product_id = prods.id and category_id = cats.id group by product_id, product_name, product_description, category_name, prods.img_url order by cnt desc LIMIT 10');
    }
    private static function getNewestProds()
    {
        return DB::select('select prods.id as product_id,currentPrice, prods.created_at ,prods.name as product_name,prods.img_url, prods.description as product_description, cats.name as category_name from categories as cats,( select id as id2, ( price - (case when CURDATE() >= sale_date3 AND UNIX_TIMESTAMP(sale_date3 ) != 0 then price3 when CURDATE() >= sale_date2 AND UNIX_TIMESTAMP(sale_date2 ) != 0 then price2 when CURDATE() >= sale_date1 AND UNIX_TIMESTAMP(sale_date1 ) != 0 then  price1 else 0 end)*0.01*price) As currentPrice  from products ) as salesManagement JOIN products as prods ON salesManagement.id2=prods.id where category_id = cats.id Order By created_at desc Limit 10');
    }
    private static function getRandomProds()
    {
        return DB::select('select prods.id as product_id,currentPrice, prods.name as product_name,prods.img_url, prods.description as product_description, cats.name as category_name from categories as cats ,( select id as id2, ( price - (case when CURDATE() >= sale_date3 AND UNIX_TIMESTAMP(sale_date3 ) != 0 then price3 when CURDATE() >= sale_date2 AND UNIX_TIMESTAMP(sale_date2 ) != 0 then price2 when CURDATE() >= sale_date1 AND UNIX_TIMESTAMP(sale_date1 ) != 0 then  price1 else 0 end)*0.01*price) As currentPrice  from products ) as salesManagement JOIN products as prods ON salesManagement.id2=prods.id where category_id = cats.id Order By RAND() Limit 10');
    }
}
