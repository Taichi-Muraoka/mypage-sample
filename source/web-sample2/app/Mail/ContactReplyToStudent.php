<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/**
 * 生徒への問い合わせ回答メール
 */
class ContactReplyToStudent extends Mailable
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
     * @param array 'admin_name'        回答者名
     *              'answer_date'       回答日
     *              'title'             件名
     *              'text'              回答内容
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
        $subject = Lang::get('message.mail.contact_reply_to_student.subject');

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.contact_reply_to_student');
    }
}
