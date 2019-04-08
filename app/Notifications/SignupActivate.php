<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SignupActivate extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $url = url('/api/v1/auth/signup/activate/'.$notifiable->activation_token);
        return (new MailMessage)
                    ->subject('Xác nhận tài khoản QC247')
                    ->line('Cảm ơn bạn đã đăng ký sử dụng dịch vụ của chúng tôi! Bạn hãy nhấn vào nút "Xác nhận" để kích hoạt tài khoản này!')
                    ->action('Xác nhận', url($url))
                    ->line('QC247 cảm ơn bạn!');
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
