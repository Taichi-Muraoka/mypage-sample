<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * お知らせ通知メール
 */
class NoticeRegistToStudent extends Mailable
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
     * @param array メール本文 タイトル
     * @return void
     */
    public function __construct($mail_body, $subject)
    {
        // 本文に使用する変数を保持
        $this->mail = $mail_body;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->subject;

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.notice_regist_to_student');
    }
}
