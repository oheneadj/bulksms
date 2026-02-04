<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('dashboard') }}"
                    class="text-[10px] font-bold text-zinc-400 hover:text-indigo-brand uppercase tracking-widest transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-zinc-300"></i>
                <span class="text-[10px] font-bold text-indigo-brand uppercase tracking-widest">Messaging</span>
            </div>
            <h2 class="text-3xl font-extrabold text-zinc-900 tracking-tight leading-tight">Campaigns</h2>
            <p class="text-zinc-500 text-sm mt-1 font-medium">Track your bulk SMS campaigns and delivery performance.
            </p>
        </div>

        <a href="{{ route('messaging.send') }}"
            class="bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-3 px-6 rounded-[6px] shadow-xl shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2 group">
            <i data-lucide="plus" class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300"></i>
            New Campaign
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm overflow-hidden flex flex-col">
        <!-- Card Header -->
        <div
            class="px-6 py-4 border-b border-zinc-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
            <div class="flex items-center gap-2">
                <h2 class="text-base font-semibold text-zinc-900">Campaign History</h2>
                <span
                    class="px-2.5 py-0.5 rounded-full bg-zinc-100 text-zinc-600 text-xs font-medium">{{ $campaigns->total() }}
                    total</span>
            </div>

            <div class="relative max-w-xs">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search campaigns..."
                    class="pl-9 pr-4 py-2 text-sm w-full bg-zinc-50 border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900">
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-100">
                <thead class="bg-zinc-50/50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Campaign Name</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Recipients</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Status</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Created At</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Total Cost</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($campaigns as $campaign)
                        <tr class="group hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-zinc-900">{{ $campaign->name }}</span>
                                    <span class="text-[10px] text-zinc-400 font-medium">{{ $campaign->sender_id }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-semibold text-zinc-900">{{ number_format($campaign->total_recipients) }}</span>
                                    @if($campaign->delivered_count > 0 || $campaign->failed_count > 0)
                                        <div class="flex gap-2 text-[10px] mt-0.5">
                                            <span class="text-emerald-600 font-medium">{{ $campaign->delivered_count }}
                                                delivered</span>
                                            <span class="text-rose-600 font-medium">{{ $campaign->failed_count }} failed</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'sending' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'scheduled' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    ];
                                    $color = $statusColors[$campaign->status] ?? 'bg-zinc-50 text-zinc-700 border-zinc-200';
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 border rounded-full text-[10px] font-bold uppercase tracking-wider {{ $color }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {{ $campaign->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="text-xs text-zinc-500 font-medium">{{ $campaign->created_at->format('M d, Y h:i A') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span
                                    class="text-sm font-bold text-zinc-900">${{ number_format($campaign->total_cost, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('messaging.campaign-analytics', $campaign->id) }}"
                                        class="p-1.5 bg-zinc-100 hover:bg-indigo-100 rounded-lg text-zinc-400 hover:text-indigo-600 transition-colors"
                                        title="View Analytics">
                                        <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                                    </a>

                                    <button wire:click="syncStatus({{ $campaign->id }})" wire:loading.attr="disabled"
                                        class="p-1.5 bg-zinc-100 hover:bg-emerald-100 rounded-lg text-zinc-400 hover:text-emerald-600 transition-colors"
                                        title="Sync Delivery Reports">
                                        <i data-lucide="refresh-cw" class="w-4 h-4" wire:loading.class="animate-spin"
                                            wire:target="syncStatus({{ $campaign->id }})"></i>
                                    </button>

                                    @if($campaign->status === 'scheduled')
                                        <button wire:click="cancel({{ $campaign->id }})"
                                            wire:confirm="Are you sure you want to cancel this scheduled campaign?"
                                            class="p-1.5 bg-zinc-100 hover:bg-amber-100 rounded-lg text-zinc-400 hover:text-amber-600 transition-colors"
                                            title="Cancel">
                                            <i data-lucide="ban" class="w-4 h-4"></i>
                                        </button>
                                    @endif

                                    @if($campaign->status !== 'sending')
                                        <button wire:click="delete({{ $campaign->id }})"
                                            wire:confirm="Are you sure you want to delete this campaign? This cannot be undone."
                                            class="p-1.5 bg-zinc-100 hover:bg-rose-100 rounded-lg text-zinc-400 hover:text-rose-600 transition-colors"
                                            title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-16 h-16 bg-zinc-50 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="layers" class="h-8 w-8 text-zinc-300"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-zinc-900 mb-1">No campaigns found</h3>
                                    <p class="text-sm text-zinc-500 mb-6">Your bulk SMS campaigns will appear here once you
                                        send them to a group.</p>
                                    <a href="{{ route('messaging.send') }}"
                                        class="text-sm font-bold text-indigo-600 hover:text-indigo-700">
                                        Launch your first campaign
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
            <div class="px-6 py-4 border-t border-zinc-100">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => lucide.createIcons());
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
    </script>
</div>