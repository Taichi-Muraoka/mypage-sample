{{ $mail['student_name'] }}さんより、問い合わせが登録されました。

宛先校舎：{{ $mail['campus_name'] }}
問い合わせ日：{{ $mail['contact_date'] }}
件名：{{ $mail['title'] }}
内容：{{ $mail['text'] }}

@include('emails.signature')