<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('dashboard') }}"
                    class="text-[10px] font-bold text-zinc-400 hover:text-indigo-brand uppercase tracking-widest transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-zinc-300"></i>
                <span class="text-[10px] font-bold text-indigo-brand uppercase tracking-widest">Developer</span>
            </div>
            <h2 class="text-3xl font-extrabold text-zinc-900 tracking-tight leading-tight">API Keys</h2>
            <p class="text-zinc-500 text-sm mt-1 font-medium">Generate and manage API keys to integrate BulkSMS with
                your own applications.</p>
        </div>
    </div>

    @if($newKey)
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4">
                <button wire:click="$set('newKey', null)" class="text-indigo-400 hover:text-indigo-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center shrink-0">
                    <i data-lucide="key" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-indigo-900 mb-1">Your new API Key</h3>
                    <p class="text-sm text-indigo-700 mb-4 font-medium italic">Make sure to copy it now. For security
                        reasons, we cannot show this key again.</p>

                    <div
                        class="flex items-center gap-2 bg-white border-2 border-indigo-100 rounded-lg px-4 py-3 shadow-sm group-hover:border-indigo-200 transition-colors">
                        <code class="text-sm font-mono font-bold text-indigo-600 break-all">{{ $newKey }}</code>
                        <button onclick="navigator.clipboard.writeText('{{ $newKey }}'); this.innerText = 'Copied!';"
                            class="shrink-0 ml-4 px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[10px] font-bold uppercase tracking-wider rounded transition-all">
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Generate New Key -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 sticky top-8">
                <h3 class="text-sm font-bold text-zinc-900 mb-6 uppercase tracking-wider">Generate New Key</h3>

                <form wire:submit="generateKey" class="space-y-4">
                    <div>
                        <label class="block text-xs font-extrabold text-zinc-500 uppercase tracking-wider mb-2">Key
                            Name</label>
                        <input type="text" wire:model="name" placeholder="e.g. My Website API"
                            class="w-full bg-zinc-50 border border-zinc-200 rounded-lg px-4 py-2.5 focus:border-indigo-500 focus:ring-0 transition-all font-medium text-sm">
                        @error('name') <p class="text-[10px] text-rose-500 font-bold uppercase tracking-wider mt-1">
                        {{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-3 px-6 rounded-lg shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Generate Key
                    </button>
                </form>

                <div class="mt-8 p-4 bg-zinc-50 rounded-lg border border-zinc-100">
                    <p class="text-[10px] text-zinc-500 leading-relaxed italic font-medium">
                        <i data-lucide="info" class="w-3 h-3 inline-block mr-1"></i>
                        API keys grant full access to your account. Do not share them and revoke any compromised keys
                        immediately.
                    </p>
                </div>
            </div>
        </div>

        <!-- Keys List -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-zinc-900 uppercase tracking-wider">Active API Keys</h3>
                    <span class="text-[10px] font-bold text-zinc-400">{{ count($apiKeys) }} total</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left font-medium text-sm">
                        <thead class="bg-zinc-50/50 text-zinc-400 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Name</th>
                                <th class="px-6 py-4">Last Used</th>
                                <th class="px-6 py-4">Created</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($apiKeys as $key)
                                <tr class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-zinc-900">{{ $key->name }}</span>
                                            <span
                                                class="text-[10px] font-mono text-zinc-400 font-bold tracking-tight uppercase">SHA256
                                                Fingerprint...</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs text-zinc-500">
                                            {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never used' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-zinc-500">
                                        {{ $key->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button wire:click="revokeKey({{ $key->id }})"
                                            wire:confirm="Are you sure you want to revoke this API key? Any applications using it will lose access immediately."
                                            class="p-2 hover:bg-rose-50 rounded-lg text-zinc-400 hover:text-rose-600 transition-all">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center opacity-40">
                                            <i data-lucide="key" class="w-10 h-10 mb-2"></i>
                                            <p class="font-bold">No API keys found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => lucide.createIcons());
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
    </script>
</div>