{{ $mail['name'] }} さんの明日の授業予定をお知らせします。

@foreach ($mail['lessons'] as $lesson)
授業日時：{{ $lesson['date_time'] }}
校舎：{{ $lesson['campus_name'] }}

@endforeach
詳細はマイページにログインし、カレンダーよりご確認ください。

@include('emails.signature')