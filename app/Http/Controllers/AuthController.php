<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserVerify;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\App;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'min:2', 'max:255', 'string'],
            'email' => ['required', 'min:2', 'max:255', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'max:255', 'confirmed', 'string'],
            'phone_number' => ['required', 'numeric'],
        ]);
        $input = $request->only('name', 'email', 'password', 'phone_number');
        $input['password'] = Hash::make($request['password']);
        if (!User::first()) { //if users table is empty then make the first user the app owner
            $input['role_id'] = 2;
        }

        $user = User::create($input);
        if (AuthController::sendCode($user, $request)) {
            $token = $user->createToken('Personal-Access-Token')->plainTextToken;
            $data["user"] = AuthController::user(User::find($user->id));
            $data["token_type"] = 'Bearer';
            $data["access_token"] = $token;
            return response()->json($data, 201);
        }
        return response()->json([__("messages.There seems to be a problem")], 400);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'min:2', 'max:255', 'email'],
            'password' => ['required', 'min:8', 'max:255', 'string'],
        ]);

        $check = $request->only('email', 'password');
        if (!Auth::attempt($check)) {
            return response()->json(["message" => __('messages.invalid data')], 401);
        }
        $user = $request->user();

        $token = $user->createToken('Personal-Access-Token')->plainTextToken;

        $data["user"] = AuthController::user($user);
        $data["token_type"] = 'Bearer';
        $data["access_token"] = $token;

        return response()->json($data, 200);
    }

    public function logout()
    {
        User::find(Auth::id())->tokens()->delete();
        return response()->json(['message' => __('messages.logged out')], 200);
    }

    public function show()
    {
        return response()->json(AuthController::user(Auth::user()), 200);
    }

    public function showMyProduct()
    {
        $user = User::find(Auth::id());
        $products = $user->products()->orderByDesc('created_at')->paginate(20);
        //pagination here created by frontend as back can't use paginate() on $data array
        //EX :http://127.0.0.1:8000/api/products/?page=1 first (20) product
        //EX :http://127.0.0.1:8000/api/products/?page=2 next (20) product
        if ($products->isEmpty()) {
            return response()->json(["message" => __('messages.You have not added any product yet')], 200);
        }
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

    public function update(Request $request)
    {
        $request->validate([
            'name' => ['min:2', 'max:255', 'string', 'nullable'],
            'email' => ['min:2', 'max:255', 'email', Rule::unique('users', 'email')->ignore(Auth::id()), 'nullable'],
            'password' => ['min:6', 'max:255', 'string', 'nullable'],
            'image' => ['image', 'mimes:jpg,png,jpeg,gif,svg,bmp', 'max:4096', 'nullable'],
            'phone_number' => ['numeric', 'nullable'],
            'whatsapp_url' => ['url', 'nullable'],
            'facebook_url' => ['url', 'nullable'],
        ]);
        $id = Auth::id();
        $user = User::find($id);
        if ($request->hasFile('image')) {
            if ($user->prof_img_url != "RrmDmqreoLbR6dhjSVuFenDAii8uBWdqhi2fYSjK9pRISPykLSdefaultprofileimg.jpg") {
                Storage::delete('public/images/users/' . $user->prof_img_url);
            }
            $destination_path = 'public/images/users';
            $image = $request->file('image');
            $randomString = Str::random(50);
            $image_name = $id . '/' . $randomString . $image->getClientOriginalName();
            $image->storeAs($destination_path, $image_name);
            $user->update([
                'prof_img_url' => $image_name
            ]);
        }
        if ($request->name) {
            $user->update([
                'name' => $request->name
            ]);
        }
        if ($request->email) { //could be updated
            $user->update([
                'email' => $request->email
            ]);
        }
        if ($request->password) {
            $request['password'] = Hash::make($request['password']);
            $user->update([
                'password' => $request->password
            ]);
        }
        if ($request->phone_number) {
            $user->update([
                'phone_number' => $request->phone_number
            ]);
        }
        if ($request->whatsapp_url) {
            $user->update([
                'whatsapp_url' => $request->whatsapp_url
            ]);
        }
        if ($request->facebook_url) {
            $user->update([
                'facebook_url' => $request->facebook_url
            ]);
        }
        return response()->json(AuthController::user($user), 200);
    }

    public function destroy()
    {
        $id = Auth::id();
        Storage::deleteDirectory('public/images/users/' . $id);
        Storage::deleteDirectory('public/images/products/' . $id);
        User::find($id)->tokens()->delete();
        User::destroy($id);
        return response()->json(["message" => __('messages.Your account has been deleted successfully')], 200);
    }

    public static function  user($user)
    {
        return [
            "id" => $user->id,
            "name" => $user->name,
            "email" => $user->email,
            "prof_img_url" => $user->prof_img_url,
            "facebook_url" => (string)$user->facebook_url,
            "whatsapp_url" => (string)$user->whatsapp_url,
            "phone_number" => (string)$user->phone_number,
            "role_id" => $user->role_id,
            "created_at" => $user->created_at->format("Y-m-d H-i-s"),
            "updated_at" => $user->updated_at->format("Y-m-d H-i-s"),
            "is_email_verified" => $user->is_email_verified,
        ];
    }

    public function verifyAccount()
    {
        request()->validate([
            'code' => ['required'],
        ]);
        $code = request()->code;
        $id = Auth::id();
        $message = __('messages.Your e-mail is already verified');
        $status = 200;
        if (Auth::user()->is_email_verified != 1) {
            $verifyUser = UserVerify::where(['user_id' => $id, 'token' => $code])->first();
            $message = __('messages.Wrong code.');
            $status = 400;
            if (!is_null($verifyUser)) {
                $user = $verifyUser->user;
                if (!$user->is_email_verified) {
                    $verifyUser->user->is_email_verified = 1;
                    $verifyUser->user->email_verified_at = Carbon::now();
                    $verifyUser->user->save();
                    $message = __('messages.Your e-mail is verified.');
                    $status = 200;
                    $verifyUser->delete();
                }
            }
        }
        return response()->json(["message" => $message], $status);
    }

    public function  sendCode($user, $request)
    {
        $code = Str::random(64);
        UserVerify::create([
            'user_id' => $user->id,
            'token' => $code
        ]);
        // Mail::send('emails.emailVerificationEmail_' . App::currentLocale(), ['code' => $code, 'name' => $user->name], function ($msg) use ($request) {
        //     $msg->to($request->email);
        //     $msg->subject(__('messages.Email Verification for') . config('app.name'));
        // });
        return true;
    }

    public function  getcode()
    {
        $id = Auth::id();
        if (User::find($id)) {
            if (User::find($id)->is_email_verified != 1) {
                $userVerify = UserVerify::query()->where('user_id', $id)->get()->first();
                $code = Str::random(64);
                if (!is_null($userVerify)) {
                    if ((Carbon::parse($userVerify->updated_at)->addMinutes(4))->gt(Carbon::now())) {
                        return response()->json(["message" => __("messages.To get new verify code you need to wait until : ") . Carbon::parse($userVerify->updated_at)->addMinutes(5)->format("h:i")], 400);
                    }
                    $userVerify->update([
                        "token" => $code
                    ]);
                } else {
                    UserVerify::create([
                        'user_id' => $id,
                        'token' => $code
                    ]);
                }
                // Mail::send('emails.emailVerificationEmail_' . App::currentLocale(), ['code' => $code, 'name' => $user->name], function ($msg) use ($request) {
                //     $msg->to($request->email);
                //     $msg->subject('Email Verification for Shopy App');
                // });
                return response()->json(["message" => "success"], 200);
            }
            return response()->json(["message" => __('messages.Your e-mail is already verified')], 400);
        }
        return response()->json(["message" => __("messages.There seems to be a problem")], 400);
    }

    public function tests()
    {
        return 'weeee';
    }
}
