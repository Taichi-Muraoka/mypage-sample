<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/**
 * 校舎への欠席申請メール
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
     * @param array 'student_name' 生徒名 'date_period' 授業日・時限 'campus_name' 校舎名 'tutor_name' 講師名
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
        $subject = Lang::get('message.mail.absent_apply_to_office.subject');

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.absent_apply_to_office');
    }
}
