<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Set Password</title>
    </head>
    <body>
        <p>Click the link below to set your password:</p>
        <a href="{{ $setPasswordLink }}">{{ $setPasswordLink }}</a>
        <p><i>This link will be expired in 24 Hrs</i></p>
        <p>From,</p>
        <p>{{ $name }}</p>
    </body>    
</html>
