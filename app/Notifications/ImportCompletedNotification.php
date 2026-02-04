<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportCompletedNotification extends Notification
{
    use Queueable;

    public $importedCount;
    public $skippedCount;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $importedCount, int $skippedCount)
    {
        $this->importedCount = $importedCount;
        $this->skippedCount = $skippedCount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Contact Import Completed')
            ->line('Your bulk contact import has finished.')
            ->line("Successfully imported: {$this->importedCount}")
            ->line("Skipped (invalid/duplicate): {$this->skippedCount}")
            ->action('View Contacts', url('/contacts'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Import Completed',
            'message' => "Imported {$this->importedCount} contacts. Skipped {$this->skippedCount}.",
            'imported_count' => $this->importedCount,
            'skipped_count' => $this->skippedCount,
        ];
    }
}
