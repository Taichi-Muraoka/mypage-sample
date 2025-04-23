{{ $mail['studentName'] }}さんより、授業欠席の連絡がありました。
授業日・時限：{{ $mail['targetDate'] }} {{ $mail['periodNo'] }}時限目
校舎：{{ $mail['campusName'] }}

@include('emails.signature')