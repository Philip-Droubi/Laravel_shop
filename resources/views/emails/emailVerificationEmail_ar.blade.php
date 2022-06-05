<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Email Verification Mail</title>
    <style>
        a {
            justify-self: center;
            max-width: 45%;
            padding: 13px 15px;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            color: whitesmoke;
            text-transform: uppercase;
            background-color: #1976d2;
            box-shadow: 0px 4px 9px 0px #3860cf9e;
            font-family: sans-serif;
            transition: 0.2s;
            font-weight: bold;
            letter-spacing: 1px;
            display: block;
            margin: auto;
        }
        a:hover {
            box-shadow: 0px 0px 0px 0px #3860cf00;
            padding: 13px 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    
</body>
</html>
<h1 dir="rtl">تأكيد البريد الإلكتروني</h1>
<p dir="rtl">مرحبا <span style=" 
    font-size: large;
    font-weight: 600;
    color: darkorange;
">{{$name}}</span>!</p>
<p dir="rtl">شكراً على الاشتراك في تطبيقنا,</p>
<p dir="rtl">نتمنى أن ينال التطبيق إعجابكم</p>
<p dir="rtl"> الرجاء تأكيد بريدك الإلكتروني من خلال الزر التالي التالي : </p>
{{-- <a href="{{ 'shpoy/verifyemail/'. $code }}">تأكيد</a> --}}
<a href="www.google.com">تأكيد</a>
<p dir="rtl">لا تقم بمشاركة هذا الرابط مع أحد .</p>
<h4 dir="rtl">شكراً,</h4>
<h4 dir="rtl">فريق تطبيق Shopy .</h4>