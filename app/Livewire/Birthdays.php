<?php

namespace App\Livewire;

use App\Models\BirthdaySetting;
use App\Models\Contact;
use App\Models\MessageTemplate;
use App\Models\ProcessedBirthday;
use App\Models\SenderId;
use App\Services\SmsService;
use Carbon\Carbon;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Livewire\Component;

class Birthdays extends Component
{
    // Settings
    public $isEnabled = false;
    public $templateId = '';
    public $senderId = '';
    public $sendTime = '09:00';

    public function mount()
    {
        $setting = BirthdaySetting::firstOrCreate(
            ['tenant_id' => auth()->user()->tenant_id],
            ['is_enabled' => false, 'send_time' => '09:00']
        );

        $this->isEnabled = $setting->is_enabled;
        $this->templateId = $setting->message_template_id;
        $this->senderId = $setting->sender_id;
        $this->sendTime = $setting->send_time ? $setting->send_time->format('H:i') : '09:00';
    }

    public function saveSettings()
    {
        $setting = BirthdaySetting::where('tenant_id', auth()->user()->tenant_id)->first();
        
        $setting->update([
            'is_enabled' => $this->isEnabled,
            'message_template_id' => $this->templateId ?: null,
            'sender_id' => $this->senderId ?: null,
            'send_time' => $this->sendTime,
        ]);

        ToastMagic::success('Birthday settings saved.');
    }

    public function sendWish($contactId)
    {
        $contact = Contact::where('tenant_id', auth()->user()->tenant_id)->findOrFail($contactId);
        $setting = BirthdaySetting::where('tenant_id', auth()->user()->tenant_id)->first();
        
        if (!$this->senderId) {
            ToastMagic::error('Please select a Sender ID in settings first.');
            return;
        }

        $template = MessageTemplate::where('tenant_id', auth()->user()->tenant_id)
            ->find($this->templateId);
            
        if (!$template) {
            ToastMagic::error('Please select a valid template.');
            return;
        }

        // Process Message
        $messageBody = $template->body;
        $replacements = [
            '{{name}}' => $contact->name,
            '{{first_name}}' => explode(' ', $contact->name)[0],
        ];
        $messageBody = str_replace(array_keys($replacements), array_values($replacements), $messageBody);

        // Send
        $smsService = app(SmsService::class);
        $result = $smsService->send($contact->phone, $messageBody);

        if (($result['status'] ?? 'error') === 'success') {
             // Create Message Record
             $msgRecord = \App\Models\Message::create([
                'user_id' => auth()->id(),
                'recipient' => $contact->phone,
                'content' => $messageBody,
                'status' => 'queued',
                'cost' => 1,
                'parts' => 1,
            ]);
            
            // Mark as processed
            ProcessedBirthday::create([
                'tenant_id' => auth()->user()->tenant_id,
                'contact_id' => $contact->id,
                'year' => now()->year,
                'message_id' => $msgRecord->id,
            ]);

            ToastMagic::success('Birthday wish sent!');
        } else {
            ToastMagic::error('Failed to send: ' . ($result['message'] ?? 'Unknown error'));
        }
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;
        $today = now();

        $todaysBirthdays = Contact::where('tenant_id', $tenantId)
            ->whereMonth('dob', $today->month)
            ->whereDay('dob', $today->day)
            ->get()
            ->map(function ($contact) use ($today, $tenantId) {
                $contact->has_sent_today = ProcessedBirthday::where('tenant_id', $tenantId)
                    ->where('contact_id', $contact->id)
                    ->where('year', $today->year)
                    ->exists();
                return $contact;
            });

        // Upcoming (next 30 days)
        // This is tricky with raw SQL across year boundaries, but for v1 simpler logic:
        $upcoming = Contact::where('tenant_id', $tenantId)
            ->whereNotNull('dob')
            ->get()
            ->filter(function ($contact) use ($today) {
                if (!$contact->dob) return false;
                
                $birthday = $contact->dob->setYear($today->year);
                if ($birthday->isPast()) {
                    $birthday->addYear();
                }
                
                return $birthday->diffInDays($today) <= 30 && !$birthday->isToday();
            })
            ->sortBy(function ($contact) use ($today) {
                $birthday = $contact->dob->setYear($today->year);
                if ($birthday->isPast()) {
                    $birthday->addYear();
                }
                return $birthday;
            });

        return view('livewire.birthdays', [
            'todaysBirthdays' => $todaysBirthdays,
            'upcomingBirthdays' => $upcoming,
            'templates' => MessageTemplate::where('tenant_id', $tenantId)->get(),
            'senderIds' => SenderId::where('tenant_id', $tenantId)->where('status', 'approved')->get(),
        ]);
    }
}
