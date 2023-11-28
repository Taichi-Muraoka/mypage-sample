<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/**
 * 講師への欠席申請メール
 */
class AbsentApplyToTeacher extends Mailable
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
     * @param array 'studentName' 生徒名 'targetDate' 授業日 'periodNo' 時限 'campusName' 校舎名
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
        $subject = Lang::get('message.mail.absent_apply_to_teacher.subject');

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.absent_apply_to_teacher');
    }
}
