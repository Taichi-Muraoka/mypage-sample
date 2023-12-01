{{ $mail['tutor_name'] }}さんより、授業振替調整依頼がありました。
（当月振替回数：{{ $mail['apply_count'] }}回目）
授業日・時限：{{ $mail['schedule_date_time'] }}
校舎：{{ $mail['campus_name'] }}
講師：{{ $mail['tutor_name'] }}
生徒：{{ $mail['student_name'] }}

@include('emails.signature')