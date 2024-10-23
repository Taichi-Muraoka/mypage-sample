<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/**
 * 校舎への問い合わせメール
 */
class ContactToOffice extends Mailable
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
     * @param array 'student_name'      生徒名
     *              'contact_date'      問い合わせ日
     *              'campus_name'       校舎名
     *              'title'             件名
     *              'text'              内容
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
        $subject = Lang::get('message.mail.contact_to_office.subject');

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.contact_to_office');
    }
}
