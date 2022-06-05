<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserVerify;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\App;

class IsVerifyEmail
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::user()->is_email_verified) {
            $id = Auth::id();
            $name = Auth::user()->name;
            $email = Auth::user()->email;
            User::find(Auth::id())->tokens()->delete();
            $code = random_int(109009, 987789);
            $userVerify = UserVerify::query()->where('user_id', $id)->get()->first();
            if ($userVerify) {
                $userVerify->update([
                    "token" => $code
                ]);
            } else {
                UserVerify::create([
                    'user_id' => $id,
                    'token' => $code
                ]);
            }
            // Mail::send('emails.emailVerificationEmail_' . App::currentLocale(), ['code' => $code, 'name' => $name], function ($msg) use ($request,  $email) {
            //     $msg->to($email);
            //     $msg->subject('Email Verification for Shopy App');
            // });
            return response()->json(['message' => __('messages.You need to confirm your account. We have sent you an activation code, please check your email.')], 450);
        }
        return $next($request);
    }
}
