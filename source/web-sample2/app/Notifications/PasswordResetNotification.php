<?php

namespace App\Notifications;

use App\Mail\PasswordReset;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    // MEMO: 実装は Illuminate\Auth\Notifications\ResetPassword と同じ

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        // リセットリンクのURLを生成
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // Toメールアドレスを取得
        $email = $notifiable->getEmailForPasswordReset();

        // 本来であればMailMessageを使い通知として返すが、HTMLメールで体裁がある程度決まっている。
        // 今回はテキストメールで送りたいので、以下のようにMailableで返却
        // MEMO: 通常はこうする。 Mail::to($email)->send(new PasswordReset());
        return (new PasswordReset($url))->to($email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
