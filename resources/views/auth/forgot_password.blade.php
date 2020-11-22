<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
        </style>
    </head>
    <body>
        <div>
            <h4>Hi {{ $user->name}},</h4>
            <p>Please click on the link below and reset your password </p>
        <a href="{{env('FRONTEND_URL')}}/change-password/{{$user->reset_code}}" target="_blank">Reset Password</a>
        </div>
    </body>
</html>
