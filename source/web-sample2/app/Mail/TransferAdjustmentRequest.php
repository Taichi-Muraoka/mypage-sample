<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/**
 * 授業振替依頼調整メール（共通）
 */
class TransferAdjustmentRequest extends Mailable
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
     * @param array 'from_name'         振替依頼登録者(生徒名 or 講師名)
     *              'schedule_date_time' 振替前授業日・時限
     *              'campus_name'       校舎名
     *              'tutor_name'        講師名
     *              'student_name'      生徒名
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
        $subject = Lang::get('message.mail.transfer_adjustment_request.subject');

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.transfer_adjustment_request');
    }
}
