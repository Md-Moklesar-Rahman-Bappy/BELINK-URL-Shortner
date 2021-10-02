<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClickQuotaExhausted extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line(__('You have exhausted your link click quota for this month.'))
            ->line(__('You can increase it by upgrading your plan.'))
            ->action('View plan options', url('/billing/upgrade'));
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
            'image' => 'warning',
            'mainAction' => [
                'Label' => 'View plan options',
                'action' => url('/billing/upgrade'),
            ],
            'lines' => [
                [
                    'content' => __('You have exhausted your link click quota for this month.'),
                ],
                [
                    'content' => __('You can increase it by upgrading your plan.'),
                ],
            ],
        ];
    }
}
