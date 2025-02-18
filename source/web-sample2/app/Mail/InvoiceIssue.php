<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

/**
 * 請求書発行通知 - メール
 */
class InvoiceIssue extends Mailable
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
     * @param array 'name' 生徒名
     *              'invoice_ym' 請求年月
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
        $subject = Lang::get('message.mail.invoice_issue.subject');

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.invoice_issue');
    }
}
