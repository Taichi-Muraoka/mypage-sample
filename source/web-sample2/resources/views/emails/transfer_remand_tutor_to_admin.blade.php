{{ $mail['from_name'] }}さんより、授業振替調整依頼が差し戻されました。
振替調整依頼を確認し、調整してください。
授業日・時限：{{ $mail['schedule_date_time'] }}
校舎：{{ $mail['campus_name'] }}
講師：{{ $mail['tutor_name'] }}
生徒：{{ $mail['student_name'] }}

@include('emails.signature')