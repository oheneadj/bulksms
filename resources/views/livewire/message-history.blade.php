<div class="px-6 md:px-10 py-10 max-w-[1440px] mx-auto text-zinc-900">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between md:items-center gap-6 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight mb-2 text-zinc-900">{{ __('Message History') }}</h1>
            <p class="text-zinc-500 font-medium">{{ __('View and track all your sent messages and delivery status.') }}
            </p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span
                    class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">{{ __('Total Sent') }}</span>
                <div class="p-1.5 bg-indigo-50 rounded-lg">
                    <i data-lucide="send" class="w-3.5 h-3.5 text-indigo-brand"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-zinc-900 tracking-tight">{{ number_format($stats['total']) }}</p>
        </div>

        <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">{{ __('Delivered') }}</span>
                <div class="p-1.5 bg-emerald-50 rounded-lg">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-emerald-600 tracking-tight">{{ number_format($stats['delivered']) }}
            </p>
        </div>

        <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span
                    class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">{{ __('Delivery Rate') }}</span>
                <div class="p-1.5 bg-indigo-50 rounded-lg">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5 text-indigo-brand"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-zinc-900 tracking-tight">{{ $stats['deliveryRate'] }}%</p>
        </div>

        <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-2">
                <span
                    class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">{{ __('Total Cost') }}</span>
                <div class="p-1.5 bg-zinc-50 rounded-lg">
                    <i data-lucide="dollar-sign" class="w-3.5 h-3.5 text-zinc-400"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-zinc-900 tracking-tight">${{ number_format($stats['totalCost'], 2) }}
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 mb-6">
        <div class="flex flex-col lg:flex-row gap-6 items-end">
            <!-- Search -->
            <div class="w-auto flex-1 w-full space-y-1.5">
                <label
                    class="w-auto text-[10px] font-bold text-zinc-400 uppercase tracking-widest ml-1">{{ __('Search Logs') }}</label>
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="block w-full pl-9 pr-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm text-zinc-900 font-medium placeholder:text-zinc-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none"
                        placeholder="{{ __('Filter by recipient or body...') }}">
                </div>
            </div>

            <!-- Status Filter -->
            <div class="w-auto space-y-1.5">
                <label
                    class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest ml-1">{{ __('Status') }}</label>
                <select wire:model.live="statusFilter"
                    class="block w-full px-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm text-zinc-900 font-bold uppercase tracking-wider text-[10px] h-[42px] focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="queued">{{ __('Queued') }}</option>
                    <option value="sent">{{ __('Sent') }}</option>
                    <option value="delivered">{{ __('Delivered') }}</option>
                    <option value="failed">{{ __('Failed') }}</option>
                    <option value="scheduled">{{ __('Scheduled') }}</option>
                </select>
            </div>

            <!-- Date From -->
            <div class="w-auto lg:w-40 space-y-1.5">
                <label
                    class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest ml-1">{{ __('From') }}</label>
                <input type="date" wire:model.live="dateFrom"
                    class="block w-full px-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm text-zinc-900 font-medium h-[42px] focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
            </div>

            <!-- Date To -->
            <div class="w-auto lg:w-40 space-y-1.5">
                <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest ml-1">{{ __('To') }}</label>
                <input type="date" wire:model.live="dateTo"
                    class="block w-full px-4 py-2.5 bg-zinc-50 border border-zinc-200 rounded-lg text-sm text-zinc-900 font-medium h-[42px] focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
            </div>

            @if($search || $statusFilter || $dateFrom || $dateTo)
                <button wire:click="clearFilters"
                    class="whitespace-nowrap px-4 py-2 h-[42px] bg-red-500 text-[10px] font-bold text-white hover:text-red-600 hover:bg-red-50 rounded-lg border border-red-100 uppercase tracking-widest flex items-center gap-2 transition-all shadow-sm">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    {{ __('Reset') }}
                </button>
            @endif
        </div>
    </div>

    <!-- Messages Table -->
    <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50/50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4">
                            <button wire:click="sortBy('recipient')"
                                class="flex items-center gap-1.5 hover:text-indigo-600 transition-colors">
                                {{ __('Recipient') }}
                                @if($sortField === 'recipient')
                                    <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="w-3.5 h-3.5"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4">{{ __('Message') }}</th>
                        <th class="px-6 py-4">
                            <button wire:click="sortBy('sender_id')"
                                class="flex items-center gap-1.5 hover:text-indigo-600 transition-colors">
                                {{ __('Sender ID') }}
                                @if($sortField === 'sender_id')
                                    <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="w-3.5 h-3.5"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4">
                            <button wire:click="sortBy('status')"
                                class="flex items-center gap-1.5 hover:text-indigo-600 transition-colors">
                                {{ __('Status') }}
                                @if($sortField === 'status')
                                    <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="w-3.5 h-3.5"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4">
                            <button wire:click="sortBy('cost')"
                                class="flex items-center justify-end gap-1.5 w-full hover:text-indigo-600 transition-colors">
                                {{ __('Cost') }}
                                @if($sortField === 'cost')
                                    <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="w-3.5 h-3.5"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4 text-right">
                            <button wire:click="sortBy('created_at')"
                                class="flex items-center justify-end gap-1.5 w-full hover:text-indigo-600 transition-colors">
                                {{ __('Sent At') }}
                                @if($sortField === 'created_at')
                                    <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="w-3.5 h-3.5"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4 text-right">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($messages as $message)
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                        {{ strtoupper(substr($message->recipient, -2)) }}
                                    </div>
                                    <span
                                        class="font-mono text-xs font-medium text-zinc-900">{{ $message->recipient }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-zinc-900 font-medium line-clamp-2 max-w-md">{{ $message->body }}</p>
                                <span class="text-[10px] text-zinc-400 font-medium">{{ $message->parts }} part(s)</span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2 py-1 bg-zinc-100 rounded text-[10px] font-bold text-zinc-700">
                                    {{ $message->sender_id ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'sent' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'queued' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'scheduled' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    ];
                                    $color = $statusColors[$message->status] ?? 'bg-zinc-50 text-zinc-700 border-zinc-200';
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 border rounded-full text-[10px] font-bold uppercase tracking-wider {{ $color }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ $message->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-xs font-bold text-zinc-900">
                                ${{ number_format($message->cost, 4) }}
                            </td>
                            <td class="px-6 py-4 text-right text-zinc-500 font-medium text-xs">
                                @if($message->sent_at)
                                    {{ $message->sent_at->format('M d, Y') }}
                                    <span class="text-zinc-300">|</span>
                                    {{ $message->sent_at->format('h:i A') }}
                                @elseif($message->scheduled_at)
                                    <span class="text-purple-600">Scheduled for
                                        {{ $message->scheduled_at->format('M d, h:i A') }}</span>
                                @else
                                    <span class="text-zinc-400">{{ __('Pending') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($message->status === 'failed')
                                    <button wire:click="retry({{ $message->id }})" wire:loading.attr="disabled"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-xs font-bold rounded-md transition-colors">
                                        <i data-lucide="refresh-cw" class="w-3 h-3" wire:loading.class="animate-spin"
                                            wire:target="retry({{ $message->id }})"></i>
                                        Retry
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 bg-zinc-50 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="inbox" class="w-6 h-6 text-zinc-300"></i>
                                    </div>
                                    <p class="text-zinc-500 font-medium">{{ __('No messages found') }}</p>
                                    <p class="text-xs text-zinc-400 mt-1">
                                        {{ __('Try adjusting your filters or send your first message') }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($messages->hasPages())
            <div class="px-6 py-4 border-t border-zinc-100">
                {{ $messages->links() }}
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => lucide.createIcons());
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
    </script>
</div>