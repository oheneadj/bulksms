<?php

namespace App\Livewire\Messaging;

use App\Models\SenderId;
use App\Models\Message;
use App\Jobs\SendMessageJob;
use App\Jobs\SendBulkSmsJob;
use App\Services\SmsService;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class SendSms extends Component
{
    #[Rule('required|string')]
    public $sender_id = '';

    #[Rule('required|string')]
    public $recipients = '';

    #[Rule('required|string|max:480')]
    public $message = '';

    public $schedule = false;
    public $targetType = 'individual'; // individual or group
    public $selectedGroupId = '';

    #[Rule('required_if:schedule,true|after:now')]
    public $scheduledAt = '';

    public $selectedTemplateId = '';
    public $groups = [];

    public function mount()
    {
        $this->groups = Auth::user()->groups()->get();
        $firstApproved = Auth::user()->senderIds()->where('status', 'active')->first();
        if ($firstApproved) {
            $this->sender_id = $firstApproved->sender_id;
        }

        if (session()->has('selected_contacts')) {
            $selectedIds = session('selected_contacts');
            $contacts = \App\Models\Contact::whereIn('id', $selectedIds)
                ->where('tenant_id', Auth::user()->tenant_id)
                ->get();
            
            $this->recipients = $contacts->pluck('phone')->implode(', ');
            $this->targetType = 'individual';
        }
    }

    public function updatedSelectedTemplateId($value)
    {
        if ($value) {
            $template = \App\Models\MessageTemplate::find($value, ['id', 'body']);
            if ($template) {
                $this->message = $template->body;
                $this->dispatch('toastMagic', 
                    status: 'success', 
                    title: __('Template Applied'), 
                    message: __('Message content updated.')
                );
            }
        }
    }

    public function sendSms(SmsService $smsService)
    {
        $this->validate([
            'sender_id' => 'required|string',
            'message' => 'required_without:selectedTemplateId|nullable|string|max:480',
            'selectedTemplateId' => 'nullable|exists:message_templates,id',
            'recipients' => 'required_if:targetType,individual',
            'selectedGroupId' => 'required_if:targetType,group',
            'scheduledAt' => 'required_if:schedule,true|nullable|date|after:now',
        ]);

        $user = Auth::user();
        $tenant = $user->tenant;

        // Determine message content
        $messageContent = $this->message;
        if ($this->selectedTemplateId) {
            $template = \App\Models\MessageTemplate::find($this->selectedTemplateId);
            if ($template) {
                $messageContent = $template->body;
            }
        }

        if (empty($messageContent)) {
            ToastMagic::error(__('Message content cannot be empty.'));
            return;
        }

        // 1. Prepare Recipient Data
        $recipientsData = [];
        if ($this->targetType === 'individual') {
            $recipientList = array_map('trim', explode(',', $this->recipients));
            foreach (array_filter($recipientList) as $to) {
                $recipientsData[] = ['phone' => $to, 'data' => []];
            }
        } elseif ($this->targetType === 'group') {
            $group = \App\Models\Group::with('contacts')->findOrFail($this->selectedGroupId);
            foreach ($group->contacts as $contact) {
                $recipientsData[] = [
                    'phone' => $contact->phone,
                    'data' => [
                        'title' => $contact->title,
                        'first_name' => $contact->first_name,
                        'surname' => $contact->surname,
                        'name' => $contact->name, // Full name for compatibility
                        'phone' => $contact->phone,
                        'email' => $contact->email,
                    ]
                ];
            }
        } elseif ($this->targetType === 'all') {
            $contacts = \App\Models\Contact::where('tenant_id', $tenant->id)->get();
            foreach ($contacts as $contact) {
                $recipientsData[] = [
                    'phone' => $contact->phone,
                    'data' => [
                        'title' => $contact->title,
                        'first_name' => $contact->first_name,
                        'surname' => $contact->surname,
                        'name' => $contact->name, // Full name for compatibility
                        'phone' => $contact->phone,
                        'email' => $contact->email,
                    ]
                ];
            }
        }

        if (empty($recipientsData)) {
            ToastMagic::error(__('No recipients found.'));
            return;
        }

        // 2. Base calculations
        $parts = $smsService->calculateParts($messageContent);
        $totalRecipients = count($recipientsData);
        $totalParts = $parts * $totalRecipients;

        // 3. Check credits
        if ($tenant->sms_credits < $totalParts) {
            ToastMagic::error(__('Insufficient credits. You need :needed credits, but only have :available.', [
                'needed' => $totalParts,
                'available' => $tenant->sms_credits
            ]));
            return;
        }

        // 4. Process sending
        DB::transaction(function () use ($user, $tenant, $recipientsData, $parts, $totalParts, $messageContent) {
            // Deduct credits
            $tenant->decrement('sms_credits', $totalParts);

            // Log Usage Transaction
            \App\Models\Transaction::create([
                'user_id' => $user->id,
                'type' => 'usage',
                'amount' => $totalParts,
                'description' => 'SMS sent to ' . count($recipientsData) . ' recipient(s)',
                'reference' => 'SMS-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10)),
                'balance_after' => $tenant->sms_credits,
            ]);

            $campaign = null;
            if ($this->targetType === 'group' || $this->targetType === 'all') {
                $campaignName = ($this->targetType === 'group') 
                    ? \App\Models\Group::find($this->selectedGroupId, ['id', 'name'])->name 
                    : __('All Contacts');
                
                $campaign = \App\Models\Campaign::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'name' => $campaignName . ' - ' . now()->format('M d, Y H:i'),
                    'sender_id' => $this->sender_id,
                    'message_body' => $messageContent,
                    'total_recipients' => count($recipientsData),
                    'total_cost' => $totalParts,
                    'status' => $this->schedule ? 'scheduled' : 'sending',
                    'scheduled_at' => $this->schedule ? \Carbon\Carbon::parse($this->scheduledAt) : null,
                ]);
            }

            // 5. Check for Bulk Optimization
            // Conditions: No personalization ({{...}}) AND not scheduled (simple queue) 
            // AND target type allows it (all types do if no vars)
            // Actually, we can check if $messageContent contains '{{'. 
            // If it DOES contain '{{', we must loop as normal.
            // If it DOES NOT, we can use SendBulkSmsJob.
            
            $hasPersonalization = str_contains($messageContent, '{{');
            
            if (!$this->schedule && !$hasPersonalization && count($recipientsData) > 1) {
                // Optimization: Bulk Send
                $messageIds = [];
                $phones = [];
                
                foreach ($recipientsData as $recipient) {
                    $messageRecord = Message::create([
                        'user_id' => $user->id,
                        'campaign_id' => $campaign?->id,
                        'sender_id' => $this->sender_id,
                        'recipient' => $recipient['phone'],
                        'body' => $messageContent,
                        'parts' => $parts,
                        'cost' => $parts,
                        'status' => 'queued',
                        'scheduled_at' => null,
                    ]);
                    $messageIds[] = $messageRecord->id;
                    $phones[] = $recipient['phone'];
                }
                
                SendBulkSmsJob::dispatch($phones, $messageContent, $this->sender_id, $campaign?->id, $messageIds);
                
            } else {
                // Standard Loop (Personalized or Single or Scheduled)
                foreach ($recipientsData as $recipient) {
                    $status = $this->schedule ? 'scheduled' : 'queued';
                    $scheduledAtTimestamp = $this->schedule ? \Carbon\Carbon::parse($this->scheduledAt) : null;

                    // Personalized message
                    $finalMessage = $messageContent;
                    foreach ($recipient['data'] as $key => $value) {
                        $finalMessage = str_replace("{{{$key}}}", $value, $finalMessage);
                    }

                    $messageRecord = Message::create([
                        'user_id' => $user->id,
                        'campaign_id' => $campaign?->id,
                        'sender_id' => $this->sender_id,
                        'recipient' => $recipient['phone'],
                        'body' => $finalMessage,
                        'parts' => $parts,
                        'cost' => $parts,
                        'status' => $status,
                        'scheduled_at' => $scheduledAtTimestamp,
                    ]);

                    if (!$this->schedule) {
                        SendMessageJob::dispatch($messageRecord);
                    }
                }
            }
        });

        if ($this->schedule) {
            $msg = __('Messages scheduled for :date', ['date' => $this->scheduledAt]);
        } else {
            $msg = __('Messages successfully queued for sending.');
        }

        ToastMagic::success($msg);
        
        $this->reset(['recipients', 'message', 'schedule', 'scheduledAt', 'selectedTemplateId']);
    }

    public function render()
    {
        return view('livewire.messaging.send-sms', [
            'senderIds' => Auth::user()->senderIds()->latest()->get(),
        ]);
    }
}
