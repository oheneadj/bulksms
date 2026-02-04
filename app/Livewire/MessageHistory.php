<?php

namespace App\Livewire;

use App\Models\Message;
use App\Jobs\SendMessageJob;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class MessageHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

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
        $query = Message::where('user_id', '=', Auth::id());

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('recipient', 'like', '%' . $this->search . '%')
                  ->orWhere('body', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', '=', $this->statusFilter);
        }

        // Apply date range filter
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $messages = $query->orderBy($this->sortField, $this->sortDirection)->paginate(15);

        // Calculate summary stats
        $totalMessages = Message::where('user_id', '=', Auth::id())->count();
        $deliveredCount = Message::where('user_id', '=', Auth::id())
            ->where('status', '=', 'delivered')
            ->count();
        $deliveryRate = $totalMessages > 0 ? round(($deliveredCount / $totalMessages) * 100, 1) : 0;
        $totalCost = Message::where('user_id', '=', Auth::id())->sum('cost');

        return view('livewire.message-history', [
            'messages' => $messages,
            'stats' => [
                'total' => $totalMessages,
                'delivered' => $deliveredCount,
                'deliveryRate' => $deliveryRate,
                'totalCost' => $totalCost,
            ],
        ]);
    }
}
