<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/**
 * 授業振替依頼調整 差戻しメール(生徒→管理者)
 */
class TransferRemandStudentToAdmin extends Mailable
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
     * @param array 'from_name'         振替依頼差戻し者(生徒名)
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
        $subject = Lang::get('message.mail.transfer_remand_student_to_admin.subject');

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.transfer_remand_student_to_admin');
    }
}
