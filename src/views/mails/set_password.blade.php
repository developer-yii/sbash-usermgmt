<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>{{ __('usermgmt')['mails']['set_password'] }}</title>
    </head>
    <body>
        <p>{{ __('usermgmt')['mails']['password_link'] }}:</p>
        <a href="{{ $setPasswordLink }}">{{ $setPasswordLink }}</a>
        <p><i>{{ __('usermgmt')['mails']['link_expired'] }}</i></p>
        <p>{{ __('usermgmt')['mails']['from'] }},</p>
        <p>{{ $name }}</p>
    </body>    
</html>
