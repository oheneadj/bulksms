<?php

namespace App\Jobs;

use App\Models\SenderId;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckSenderIdStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        // Find all sender IDs that are pending
        $pendingIds = SenderId::where('status', 'pending')->get();
        
        Log::info("CheckSenderIdStatusJob: Found {$pendingIds->count()} pending sender IDs.");

        foreach ($pendingIds as $senderId) {
            try {
                // Wait a bit to avoid rate limits if many
                if ($senderId !== $pendingIds->first()) {
                    sleep(1); 
                }

                $result = $smsService->checkSenderIdStatus($senderId->sender_id);
                
                if (($result['status'] ?? 'error') === 'success') {
                    $mappedStatus = $result['mapped_status'] ?? 'pending';
                    
                    if ($senderId->status !== $mappedStatus) {
                        Log::info("Updating Sender ID {$senderId->sender_id} status to {$mappedStatus}");
                        $senderId->update(['status' => $mappedStatus]);
                        
                        // Optional: Notify user via email/notification here
                    }
                } else {
                    Log::warning("Failed to check status for {$senderId->sender_id}: " . ($result['message'] ?? 'Unknown error'));
                }

            } catch (\Exception $e) {
                Log::error("Error processing Sender ID {$senderId->id}: " . $e->getMessage());
            }
        }
    }
}
