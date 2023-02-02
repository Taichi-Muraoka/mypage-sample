{{ $mail['name'] }}さんより、授業欠席の連絡がありました。
授業日時：{{ $mail['datetime'] }}
教室：{{ $mail['room_name'] }}


@include('emails.signature')