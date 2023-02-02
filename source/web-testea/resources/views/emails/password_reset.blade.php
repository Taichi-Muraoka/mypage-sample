パスワードリセットのリクエストを受け付けました。

以下のURLにアクセスしてパスワードを設定してください。
{{ $url }}

有効期限は{{config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')}}分です。

もしお心当たりのない場合、本メールは破棄して頂けるようお願いいたします。


@include('emails.signature')