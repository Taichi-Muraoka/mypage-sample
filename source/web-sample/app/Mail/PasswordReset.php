<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/**
 * パスワードリセット - メール
 */
class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * URL
     *
     * @var string URL
     */
    public $url;

    /**
     * Create a new message instance.
     *
     * @param string $url URL
     * @return void
     */
    public function __construct($url)
    {
        // 本文に使用するURLを保持
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = Lang::get('message.mail.password_reset.subject');

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.password_reset');
    }
}
