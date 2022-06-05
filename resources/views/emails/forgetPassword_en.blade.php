<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset Password Notification</title>
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
<h1>Reset Password Notification</h1>
Hey <span style=" 
    font-size: large;
    font-weight: 600;
    color: darkorange;
">{{$name}}</span>!
<p>We have received a password reset request from your account</p>
<p>Please verify your email with bellow button :</p>
    {{-- <a href="{{ 'shpoy/ResetPasswordVerify/'. $token }}">verify</a> --}}
    <a href="www.google.com">verify</a>
<p><span style="color: red ;font-weight: 600; "> IF </span> you are not the one who made this request, please do not share this link with anyone.</p>
<p>
    The code will automaticlly expire after 20 minutes from your password reset request,
    you will have to request a new code after this time has passed.
</p>
<h4>Thanks,</h4>
<h4>The Shopy team.</h4>
</body>
</html>
