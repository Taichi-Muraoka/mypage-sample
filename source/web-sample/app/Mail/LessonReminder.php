<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/**
 * 翌日授業リマインド - メール
 */
class LessonReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * URL
     *
     * @var string URL
     */
    public $mail;

    /**
     * Create a new message instance.
     *
     * @param array 'name' 生徒名・講師名
     *              'lesson' 授業情報
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
        $subject = Lang::get('message.mail.lesson_reminder.subject');

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.lesson_reminder');
    }
}
