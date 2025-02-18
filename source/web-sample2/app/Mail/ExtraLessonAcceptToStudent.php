<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/**
 * 生徒への追加授業登録メール
 */
class ExtraLessonAcceptToStudent extends Mailable
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
     * @param array 'targetDate' 授業日 'periodNo' 時限 'campusName' 校舎名 'tutorName' 講師名
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
        $subject = Lang::get('message.mail.extra_lesson_accept_to_student.subject');

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.extra_lesson_accept_to_student');
    }
}
