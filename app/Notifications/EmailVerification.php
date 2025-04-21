<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class EmailVerification extends VerifyEmailNotification
{
    use Queueable;

    protected $customData;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($customData)
    {
        $this->customData = $customData;
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
        $verificationUrl = $this->verificationUrl($notifiable);
        return (new MailMessage)
                    ->subject('Verify Your Email Address')
                    // ->line('The introduction to the notification.')
                    // ->action('Notification Action', url('/'))
                    // ->line('Thank you for using our application!')
                    ->markdown('backend.notification.email', [
                        'introLines' => ['Please click the button below to verify your email address.'],
                        'actionText' => 'Verify your Email',
                        'actionUrl' => $verificationUrl,
                        'outroLines' => ['If you did not create an account, no further action is required.'],
                        'displayableActionUrl' => $verificationUrl,
                        'customData' => $this->customData,
                    ]);
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
