<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function submitForgetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);
        $token = Str::random(64);
        if (!DB::table('password_resets')->where('email', $request->email)->first()) {
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
        } else {
            DB::table('password_resets')->where('email', $request->email)->limit(1)->update([
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
        }

        // Mail::send('emails.forgetPassword_' . App::currentLocale(), ['token' => $token, 'name' => User::query()->where('email', request()->email)->first()->name], function ($msg) use ($request) {
        //     $msg->to($request->email);
        //     $msg->subject(__('messages.Reset Password Notification') . config('app.name'));
        // });
        return response()->json(["message" => __("messages.You need to confirm your email. We have sent you a Verification Code, please check your email.")], 200);
    }

    /* public function verifytoken2($token)
    {
        $rp = DB::table('password_resets')->where('token', $token)->first(); //rp = reset password record in DB
        if ($rp) {
            if ((Carbon::parse($rp->created_at)->addMinutes(20))->gte(Carbon::now())) {
                DB::table('password_resets')->where('token', $token)->limit(1)->update([
                    'is_verified' => 1,
                    'token' => Str::random(64)
                ]);
                return response()->json(['message' => 'Success', 'code' => $token], 200);
            }
            DB::table('password_resets')->where('token', $token)->limit(1)->update([
                'token' => Str::random(64),
                'created_at' => Carbon::now()
            ]);
            // Mail::send('emails.forgetPassword_' . App::currentLocale(), ['token' => $token, 'name' => User::query()->where('email', request()->email)->first()->name], function ($msg) use ($request) {
            //     $msg->to($request->email);
            //     $msg->subject(__('messages.Reset Password Notification') . config('app.name'));
            // });
            return response()->json(["message" => __("messages.This code has Expired. We have sent you a new Verification Code, please check your email.")], 400);
        }
        return response()->json(["message" => __("messages.Wrong code.")], 400);
    }*/

    public function verifytoken(Request $request)
    {
        request()->validate([
            'token' => ['required', 'string'],
        ]);
        $token = request()->token;
        $rp = DB::table('password_resets')->where('token', $token)->first(); //rp = reset password record in DB
        if ($rp) {
            if (((Carbon::parse($rp->created_at)->addMinutes(20))->gte(Carbon::now())) && $rp->is_verified != 1) {
                DB::table('password_resets')->where('token', $token)->limit(1)->update([
                    'is_verified' => 1,
                    'token' => $token = Str::random(64)
                ]);
                return response()->json(['message' => 'Success', 'code' => $token], 200);
            }
            DB::table('password_resets')->where('token', $token)->limit(1)->update([
                'token' => Str::random(64),
                'created_at' => Carbon::now()
            ]);
            // Mail::send('emails.forgetPassword_' . App::currentLocale(), ['token' => $token, 'name' => User::query()->where('email', request()->email)->first()->name], function ($msg) use ($request) {
            //     $msg->to($request->email);
            //     $msg->subject(__('messages.Reset Password Notification') . config('app.name'));
            // });
            return response()->json(["message" => __("messages.This code has Expired. We have sent you a new Verification Code, please check your email.")], 400);
        }
        return response()->json(["message" => __("messages.Wrong code.")], 400);
    }

    public function resetpassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'min:8', 'max:255', 'confirmed', 'string'],
            'code' => ['required', 'string']
        ]);
        $updatePassword = DB::table('password_resets')
            ->where(['email' => $request->email, 'is_verified' => 1, 'token' => request()->code])->first();
        if (!$updatePassword) {
            return response()->json(["message" => __("messages.invalid data")], 400);
        }
        $user = User::query()->where('email', $updatePassword->email)->first();
        $user->update(['password' => Hash::make($request->password)]);
        DB::table('password_resets')->where(['email' => $updatePassword->email])->delete();

        // Mail::send('emails.resetPasswordconfirm_' . App::currentLocale(), ['name' => $user->name, 'time' => Carbon::now()->format('Y-m-d H:i:s')], function ($msg) use ($request) {
        //     $msg->to($request->email);
        //     $msg->subject(__('messages.Reset Password confirmation') . config('app.name'));
        // });

        $user->tokens()->delete();
        return response()->json(["message" => __("messages.Your password has been changed!")], 200);
    }
}
