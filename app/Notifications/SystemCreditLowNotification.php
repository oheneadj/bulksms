<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemCreditLowNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $currentBalance;

    /**
     * Create a new notification instance.
     */
    public function __construct($currentBalance)
    {
        $this->currentBalance = $currentBalance;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // Add 'database' if we had a notifications table UI for admins
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('⚠️ System SMS Credits Low')
                    ->greeting('Hello Admin,')
                    ->line('The system SMS credit balance has dropped below the threshold or was insufficient for a purchase request.')
                    ->line('Current Balance: ' . number_format($this->currentBalance))
                    ->action('Restock Credits', url('/admin/packages')) // Assuming this or similar route exists
                    ->line('Please restock immediately to ensure uninterrupted service.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'balance' => $this->currentBalance,
            'message' => 'System credits low: ' . $this->currentBalance
        ];
    }
}
