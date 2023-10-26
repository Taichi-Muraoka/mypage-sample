<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/**
 * 再入会生徒へのマイページログイン案内メール
 */
class MypageGuideRejoinToStudent extends Mailable
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
     * @param array
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
        // $subject = "【" . config('app.name') . "】" . Lang::get('message.mail.mypage_guide_to_student.subject');
        $subject = "【個別指導塾テスティー】" . Lang::get('message.mail.mypage_guide_rejoin_to_student.subject');

        return $this
            // タイトル
            ->subject($subject)
            // テンプレート
            ->text('emails.mypage_guide_rejoin_to_student');
    }
}
