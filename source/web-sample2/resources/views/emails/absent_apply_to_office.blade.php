{{ $mail['student_name'] }}さんより、授業欠席の申請がありました。
授業日・時限：{{ $mail['date_period'] }}
校舎：{{ $mail['campus_name'] }}
講師名：{{ $mail['tutor_name'] }}

@include('emails.signature')