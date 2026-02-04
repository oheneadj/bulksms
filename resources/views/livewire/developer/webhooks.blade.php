<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-zinc-900 tracking-tight">Webhooks</h1>
            <p class="text-zinc-500 font-medium mt-1">Receive real-time updates for message delivery and other events.
            </p>
        </div>
    </div>
    <div class="flex items-center gap-4">
        <!-- Simulation Toggle -->
        <label
            class="flex items-center gap-2 cursor-pointer bg-white border border-zinc-200 px-4 py-2 rounded-lg shadow-sm hover:border-indigo-300 transition-all">
            <div class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" wire:model.live="simulateWebhooks" class="sr-only peer">
                <div
                    class="w-9 h-5 bg-zinc-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600">
                </div>
            </div>
            <span class="text-sm font-bold text-zinc-700">Simulate Delivery</span>
        </label>

        <button wire:click="$set('showCreateModal', true)"
            class="bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-3 px-6 rounded-[6px] shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Endpoint
        </button>
    </div>


    @if($secretToCopy)
        <div
            class="bg-emerald-50 border border-emerald-200 rounded-xl p-6 flex flex-col gap-4 animate-in fade-in slide-in-from-top-4">
            <div class="flex items-start gap-4">
                <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
                    <i data-lucide="key" class="w-6 h-6"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-emerald-900 uppercase tracking-wide">Webhook Secret Created</h3>
                    <p class="text-sm text-emerald-700 mt-1">This is the only time we'll show you this secret. Please copy
                        it now.</p>

                    <div class="mt-3 flex items-center gap-2 bg-white border border-emerald-200 rounded-lg p-2">
                        <code class="text-sm font-mono font-bold text-zinc-800 flex-1">{{ $secretToCopy }}</code>
                        <button onclick="navigator.clipboard.writeText('{{ $secretToCopy }}')"
                            class="p-2 hover:bg-emerald-50 rounded text-emerald-600 transition-colors">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <button wire:click="$set('secretToCopy', null)" class="text-emerald-400 hover:text-emerald-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6">
        @forelse($webhooks as $webhook)
            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 transition-all hover:border-indigo-200">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div
                            class="p-3 {{ $webhook->is_active ? 'bg-indigo-50 text-indigo-600' : 'bg-zinc-100 text-zinc-400' }} rounded-xl">
                            <i data-lucide="webhook" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-base font-bold text-zinc-900 max-w-md truncate">{{ $webhook->url }}</h3>
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $webhook->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-zinc-100 text-zinc-500 border border-zinc-200' }}">
                                    {{ $webhook->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </div>
                            <p class="text-xs text-zinc-500 font-mono mt-1">
                                Secret: {{ Str::mask($webhook->secret, '*', 4, -4) }}
                            </p>
                            <div class="flex gap-2 mt-3">
                                @foreach($webhook->events as $event)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md bg-zinc-50 text-zinc-600 text-[10px] font-bold border border-zinc-200">
                                        {{ $event }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button wire:click="testWebhook({{ $webhook->id }})"
                            class="px-3 py-1.5 text-xs font-bold text-zinc-600 bg-zinc-50 border border-zinc-200 rounded-lg hover:bg-zinc-100 hover:text-zinc-900 transition-all flex items-center gap-2">
                            <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                            Test
                        </button>
                        <button wire:click="toggleStatus({{ $webhook->id }})"
                            class="px-3 py-1.5 text-xs font-bold text-zinc-600 bg-zinc-50 border border-zinc-200 rounded-lg hover:bg-zinc-100 hover:text-zinc-900 transition-all">
                            {{ $webhook->is_active ? 'Disable' : 'Enable' }}
                        </button>
                        <button wire:click="deleteWebhook({{ $webhook->id }})"
                            class="px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 border border-rose-100 rounded-lg hover:bg-rose-100 hover:text-rose-700 transition-all">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white border border-zinc-200 rounded-xl">
                <div class="w-16 h-16 bg-zinc-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="webhook" class="w-8 h-8 text-zinc-300"></i>
                </div>
                <h3 class="text-lg font-bold text-zinc-900">No Webhooks Configured</h3>
                <p class="text-sm text-zinc-500 max-w-sm mx-auto mt-1 mb-6">Create a webhook to receive real-time events
                    when your messages are processed.</p>
                <button wire:click="$set('showCreateModal', true)" class="text-indigo-600 font-bold hover:underline">
                    Add your first webhook
                </button>
            </div>
        @endforelse
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
                <div class="p-6 border-b border-zinc-100 flex justify-between items-center">
                    <h3 class="text-lg font-extrabold text-zinc-900 tracking-tight">Add Webhook Endpoint</h3>
                    <button wire:click="$set('showCreateModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form wire:submit.prevent="createWebhook" class="p-6 space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Endpoint URL</label>
                        <input type="url" wire:model="url" placeholder="https://api.yoursite.com/webhooks/sms"
                            class="w-full bg-zinc-50 border border-zinc-200 rounded-lg px-4 py-3 focus:border-indigo-500 focus:ring-0 font-mono text-sm">
                        @error('url') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-3">
                        <label class="text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Events to Send</label>
                        <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-4 space-y-3">
                            @foreach($events as $event)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" wire:model="selectedEvents" value="{{ $event }}"
                                        class="w-4 h-4 text-indigo-600 border-zinc-300 rounded focus:ring-indigo-500">
                                    <span
                                        class="text-sm font-bold text-zinc-700 group-hover:text-indigo-700 transition-colors">{{ $event }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedEvents') <p class="text-rose-600 text-xs font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" wire:click="$set('showCreateModal', false)"
                            class="px-4 py-2 text-sm font-bold text-zinc-600 hover:text-zinc-900">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-indigo-brand hover:bg-indigo-brand-hover text-white text-sm font-bold rounded-lg shadow-lg shadow-indigo-500/20 active:scale-95 transition-all">
                            Add Endpoint
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
</div>