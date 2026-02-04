<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use App\Services\MessageService; // Assuming service exists or we dispatch job directly
// use App\Jobs\SendMessageJob; 

class ProcessScheduledMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch scheduled messages that are due for sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        
        $messages = Message::where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->get();

        $count = $messages->count();

        if ($count === 0) {
            $this->info('No scheduled messages due.');
            return;
        }

        $this->info("Processing {$count} scheduled messages...");

        foreach ($messages as $message) {
            // Update status to prevent double processing
            // Assuming Message model extends Eloquent Model properly
            $message->status = 'queued';
            $message->save();
            
            // Dispatch the job (Mocked for now as per plan, or log)
            \App\Jobs\SendMessageJob::dispatch($message);
            
            $this->info("Queued message ID: {$message->id}");
        }

        $this->info('All due messages have been queued.');
    }
}
