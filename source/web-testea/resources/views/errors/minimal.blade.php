<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body {margin: 0; padding: 0;}
            .container {padding: 2em 0 0;}
            p {line-height: 1.2em; margin: 0; text-align: center;}
            .code {font-size:28px; font-weight: bold; color: #333;}
            .message {font-size: 16px; color: #333;}
            .home {margin-top: 2em;}
            .home a {color: #1f4d98;}
            .home a:hover {color: #1f4d98; text-decoration: none;}
        </style>
        <title>@yield('title')</title>
    </head>
    <body>
        <div class="container">
            <p class="code">@yield('code')</p>
            <p class="message">@yield('message')</p>
            <p class="home"><a href="{{ config("app.url") }}">ホーム画面に戻る</a></p>
        </div>
    </body>
</html>
