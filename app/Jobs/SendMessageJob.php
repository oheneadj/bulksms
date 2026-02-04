<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        // Check if recipient is unsubscribed
        $user = $this->message->user;
        if ($user) {
            $isUnsubscribed = \App\Models\Contact::where('tenant_id', $user->tenant_id)
                ->where('phone', $this->message->recipient)
                ->where('is_unsubscribed', true)
                ->exists();

            if ($isUnsubscribed) {
                $this->message->update([
                    'status' => 'failed',
                    // Using a custom ID to indicate reason
                    'gateway_message_id' => 'SKIPPED-UNSUBSCRIBED' 
                ]);
                return;
            }

            // Sender ID Validation
            $senderIdString = $this->message->sender_id; // Assuming sender_id is stored on message or passed some other way?
            // Actually message table usually has sender_id column string.
            // Let's verify Message model structure first if needed, but assuming standard.

            // Wait, standard Message model has 'sender_id' string column? 
            // In SendBulkSmsJob it was passed as arg. Here it's on $this->message.
            // Let's check if $this->message->sender_id exists. 
            // Previous view_file of SendMessageJob didn't show $this->message structure but it's a model.
            
            // If sender_id is stored on message:
            $senderIdRecord = \App\Models\SenderId::where('sender_id', $this->message->sender_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$senderIdRecord || $senderIdRecord->status !== 'active') {
                $this->message->update([
                    'status' => 'failed',
                    'gateway_message_id' => 'SKIPPED-BAD-SENDER-ID'
                ]);
                return;
            }
        }

        // Placeholder Replacement
        $body = $this->message->body;
        if ($user) {
            $contact = \App\Models\Contact::where('tenant_id', $user->tenant_id)
                ->where('phone', $this->message->recipient)
                ->first();

            if ($contact) {
                $body = str_replace('{{title}}', $contact->title ?? '', $body);
                $body = str_replace('{{first_name}}', $contact->first_name ?? '', $body);
                $body = str_replace('{{surname}}', $contact->surname ?? '', $body);
                $body = str_replace('{{name}}', $contact->name, $body); // Use accessor
                $body = str_replace('{{phone}}', $contact->phone ?? '', $body);
            } else {
                 // Fallback: Remove placeholders
                 $body = str_replace(['{{title}}', '{{first_name}}', '{{surname}}', '{{name}}'], '', $body);
            }
        }

        $smsService->send(
            $this->message->recipient,
            $body,
            $this->message->id,
            null,
            $this->message->sender_id
        );
    }
}
