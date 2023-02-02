<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/**
 * 事務局への欠席申請メール
 */
class AbsentApplyToOffice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * URL
     *
     * @var array
     */
    public $mail;

    /**
     * Create a new message instance.
     *
     * @param array 'sid' 生徒No. 'name' 生徒名 'datetime' 授業日 開始時間 'room_name' 教室名 'teacher_name' 教師名 'absent_reason' 欠席申請理由 
     * @return void
     */
    public function __construct($mail_body)
    {
        // 本文に使用する変数を保持
        $this->mail = $mail_body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "【" . config('app.name') . "】" . Lang::get('message.mail.absent_apply_to_office.subject');

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.absent_apply_to_office');
    }
}
