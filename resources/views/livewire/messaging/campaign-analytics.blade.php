<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('messaging.campaigns') }}"
                    class="text-[10px] font-bold text-zinc-400 hover:text-indigo-brand uppercase tracking-widest transition-colors">Campaigns</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-zinc-300"></i>
                <span class="text-[10px] font-bold text-indigo-brand uppercase tracking-widest">{{ $campaign->name }}</span>
            </div>
            <h1 class="text-3xl font-extrabold text-zinc-900 tracking-tight">Analytics Report</h1>
            <p class="text-zinc-500 font-medium mt-1">Detailed breakdown of campaign performance.</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="syncStatus" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-zinc-200 text-zinc-700 font-bold rounded-lg hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-colors shadow-sm">
                <i data-lucide="refresh-cw" class="w-4 h-4" wire:loading.class="animate-spin" wire:target="syncStatus"></i>
                <span wire:loading.remove wire:target="syncStatus">Sync Status</span>
                <span wire:loading wire:target="syncStatus">Syncing...</span>
            </button>

            <button wire:click="exportCsv" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-zinc-200 text-zinc-700 font-bold rounded-lg hover:bg-zinc-50 transition-colors shadow-sm">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span wire:loading.remove wire:target="exportCsv">Export CSV</span>
                <span wire:loading wire:target="exportCsv">Generating...</span>
            </button>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <p class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-1">Delivery Rate</p>
            <p class="text-3xl font-extrabold text-zinc-900">{{ $stats['delivery_rate'] }}%</p>
            <div class="w-full bg-zinc-100 rounded-full h-1.5 mt-3">
                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $stats['delivery_rate'] }}%"></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <p class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-1">Total Cost</p>
            <p class="text-3xl font-extrabold text-zinc-900">{{ number_format($stats['cost'], 2) }}</p>
            <p class="text-xs text-zinc-400 font-medium mt-1">Credits Used</p>
        </div>
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <p class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-1">Delivered</p>
            <p class="text-3xl font-extrabold text-emerald-600">{{ number_format($stats['delivered']) }}</p>
            <p class="text-xs text-emerald-600/60 font-medium mt-1">Messages</p>
        </div>
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <p class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-1">Failed</p>
            <p class="text-3xl font-extrabold text-rose-600">{{ number_format($stats['failed']) }}</p>
            <p class="text-xs text-rose-600/60 font-medium mt-1">Messages</p>
        </div>
    </div>

    <!-- Message List -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="font-bold text-zinc-900">Recipient Activity</h2>
            
            <div class="flex flex-col md:flex-row gap-3">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                    <input wire:model.live.debounce.300ms="search" type="text" 
                        class="pl-9 pr-4 py-2 text-sm border border-zinc-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" 
                        placeholder="Search phone number...">
                </div>
                
                <select wire:model.live="filterStatus" class="py-2 pl-3 pr-8 text-sm border border-zinc-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="delivered">Delivered</option>
                    <option value="failed">Failed</option>
                    <option value="sent">Sent</option>
                    <option value="queued">Queued</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50/50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Recipient</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Sent At</th>
                        <th class="px-6 py-3">Gateway ID/Error</th>
                        <th class="px-6 py-3 text-right">Cost</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($messages as $message)
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4 font-mono font-medium text-zinc-900">
                                {{ $message->recipient }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClasses = match ($message->status) {
                                        'delivered' => 'bg-emerald-100 text-emerald-700',
                                        'failed' => 'bg-rose-100 text-rose-700',
                                        'sent' => 'bg-blue-100 text-blue-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded textxs font-bold uppercase tracking-wider {{ $statusClasses }}">
                                    {{ $message->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-zinc-500">
                                {{ $message->sent_at ? $message->sent_at->format('M d H:i:s') : 'Pending' }}
                            </td>
                            <td class="px-6 py-4 text-zinc-400 font-mono text-xs">
                                {{ $message->gateway_message_id ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-zinc-900">
                                {{ number_format($message->cost, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-zinc-500">
                                No messages found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($messages->hasPages())
            <div class="px-6 py-4 border-t border-zinc-100 bg-zinc-50/50">
                {{ $messages->links() }}
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => lucide.createIcons());
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
    </script>
</div>
