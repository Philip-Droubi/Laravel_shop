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
    <h1>Email Verification Mail</h1>
    Hey <span style=" 
        font-size: large;
        font-weight: 600;
        color: darkorange;
    ">{{$name}}</span>!
    <p>Thank you for regist in our app,</p>
    <p>We hope that you will like our application.</p>
    <p>Please verify your email with bellow button : </p>  
    {{-- <a href="{{ 'shpoy/verifyemail/'. $code }}">verify</a> --}}
    <a href="www.google.com">verify</a>
    <p> Do not share this link with anyone.</p>
    <h4>Thanks,</h4>
    <h4>The Shopy team.</h4>
</body>
</html>
