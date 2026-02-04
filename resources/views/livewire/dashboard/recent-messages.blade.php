<div class="lg:col-span-2 bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden">
    <div class="p-6 border-b border-zinc-100 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-zinc-900 tracking-tight">Recent Messages</h2>
            <p class="text-sm text-zinc-500 font-medium mt-1">Latest SMS delivery activity</p>
        </div>
        <a href="{{ route('messaging.history') }}"
            class="text-sm font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 transition-colors">
            View all
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50/50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                <tr>
                    <th class="px-6 py-3">Recipient</th>
                    <th class="px-6 py-3">Message</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Time</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse ($recentMessages as $message)
                    <tr class="hover:bg-zinc-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                    {{ strtoupper(substr($message->recipient, -2)) }}
                                </div>
                                <span class="font-mono text-xs font-medium text-zinc-900">{{ $message->recipient }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-zinc-900 font-medium line-clamp-1 max-w-xs">{{ $message->body }}</p>
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
                        <td class="px-6 py-4 text-right text-zinc-500 font-medium text-xs">
                            {{ $message->created_at->diffForHumans() }}
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
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 bg-zinc-50 rounded-full flex items-center justify-center mb-3">
                                    <i data-lucide="inbox" class="w-6 h-6 text-zinc-300"></i>
                                </div>
                                <p class="text-zinc-500 font-medium">No messages yet</p>
                                <p class="text-xs text-zinc-400 mt-1">Send your first SMS to get started</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>