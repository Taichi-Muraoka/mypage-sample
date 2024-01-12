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
     * @return void
     */
    public function __construct()
    {

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
