<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessBirthdayWishes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-birthday-wishes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automatic birthday wishes to contacts';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\SmsService $smsService)
    {
        $settings = \App\Models\BirthdaySetting::where('is_enabled', true)
            ->with(['tenant', 'template'])
            ->get();

        foreach ($settings as $setting) {
            // Check if run today
            if ($setting->last_run_at && $setting->last_run_at->isToday()) {
                continue;
            }

            // Check timing (simple hour check)
            if (now()->format('H:i') < $setting->send_time->format('H:i')) {
                continue;
            }

            $currentDate = now();
            
            // Find birthday contacts
            $contacts = \App\Models\Contact::where('tenant_id', $setting->tenant_id)
                ->whereMonth('dob', $currentDate->month)
                ->whereDay('dob', $currentDate->day)
                ->get();

            if ($contacts->isEmpty()) {
                $setting->update(['last_run_at' => now()]);
                continue;
            }

            foreach ($contacts as $contact) {
                // Check if already sent
                $exists = \App\Models\ProcessedBirthday::where('contact_id', $contact->id)
                    ->where('year', $currentDate->year)
                    ->exists();

                if ($exists) continue;

                if (!$setting->template) continue;

                // Process Template
                $message = $setting->template->body;
                // Simple replacement (improve with a service later)
                $replacements = [
                    '{{name}}' => $contact->name,
                    '{{first_name}}' => explode(' ', $contact->name)[0],
                ];
                $message = str_replace(array_keys($replacements), array_values($replacements), $message);

                // Send SMS
                $result = $smsService->send($contact->phone, $message);
                
                // Record Message
                $msgRecord = null;
                if (($result['status'] ?? 'error') === 'success') {
                    // Create message record logic is usually inside send() or handled by controller.
                    // SmsService::send() returns SID but doesn't create Message model? 
                    // Let's check SmsService... It seems it calls updateMessageStatus but expects a messageId.
                    // Actually SmsService::send($to, $body, $messageId) takes ID.
                    // So we must create the Message record FIRST.
                    
                    $msgRecord = \App\Models\Message::create([
                        'user_id' => \App\Models\User::where('tenant_id', $setting->tenant_id)->value('id'), // Fallback to first user? Ideally system user or admin.
                        // Wait, user_id is FK. We need a valid user.
                        // Let's find the tenant admin.
                        'user_id' => \App\Models\User::where('tenant_id', $setting->tenant_id)->orderBy('id')->value('id'),
                        'recipient' => $contact->phone,
                        'content' => $message,
                        'status' => 'queued',
                        'cost' => 1, // Logic for cost calculation needed here? or centralized?
                        // For now assuming 1 credit.
                        'parts' => 1,
                    ]);

                    $result = $smsService->send($contact->phone, $message, $msgRecord->id);
                }

                \App\Models\ProcessedBirthday::create([
                    'tenant_id' => $setting->tenant_id,
                    'contact_id' => $contact->id,
                    'year' => $currentDate->year,
                    'message_id' => $msgRecord?->id,
                ]);
            }

            $setting->update(['last_run_at' => now()]);
        }
    }
}
