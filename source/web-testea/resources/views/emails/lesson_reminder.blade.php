{{ $mail['name'] }}さんの明日の授業予定をお知らせします。

@foreach ($mail['lessons'] as $lesson)
授業日時：{{ $lesson['date_time'] }}
校舎・ブース：{{ $lesson['campus_name'] }}
科目：{{ $lesson['subject_name'] }}
@if($lesson['tutor_name'] != null)
講師：{{ $lesson['tutor_name'] }}
@endif
@if($lesson['student_name'] != null)
生徒：{{ $lesson['student_name'] }}
@endif

@endforeach
詳細はマイページにログインし、カレンダーよりご確認ください。

@include('emails.signature')