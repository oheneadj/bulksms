<?php

namespace App\Livewire\Dashboard;

use App\Jobs\SendMessageJob;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class RecentMessages extends Component
{
    public function retry($id)
    {
        $message = Message::where('user_id', Auth::id())->find($id);

        if (!$message) {
            ToastMagic::error('Message not found.');
            return;
        }

        if ($message->status === 'queued') {
             ToastMagic::info('Message is already queued.');
             return;
        }

        // Reset status
        $message->update(['status' => 'queued']);

        // Dispatch Job
        SendMessageJob::dispatch($message);

        ToastMagic::success('Message queued for retry.');
    }

    public function render()
    {
        $recentMessages = Message::where('user_id', Auth::id())
            ->latest()
            ->take(8)
            ->get();

        return view('livewire.dashboard.recent-messages', [
            'recentMessages' => $recentMessages
        ]);
    }
}
