{{ $mail['name'] }}さんより、授業欠席の申請がありました。
生徒No.：{{ $mail['sid'] }}
生徒名：{{ $mail['name'] }}
授業日時：{{ $mail['datetime'] }}
教室：{{ $mail['room_name'] }}
教師名：{{ $mail['teacher_name'] }}
欠席理由：{{ $mail['absent_reason'] }}


@include('emails.signature')