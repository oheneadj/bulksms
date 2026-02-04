<div class="space-y-8" x-data="{ showRejectModal: false }" @close-modal.window="showRejectModal = false">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('dashboard') }}"
                    class="text-[10px] font-bold text-zinc-400 hover:text-indigo-brand uppercase tracking-widest transition-colors">Admin</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-zinc-300"></i>
                <span class="text-[10px] font-bold text-indigo-brand uppercase tracking-widest">Sender ID
                    Approval</span>
            </div>
            <h2 class="text-3xl font-extrabold text-zinc-900 tracking-tight leading-tight">Manage Sender IDs</h2>
            <p class="text-zinc-500 text-sm mt-1 font-medium">Review and approve or reject sender ID requests from
                tenants.</p>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm overflow-hidden flex flex-col">
        <!-- Card Header -->
        <div
            class="px-6 py-4 border-b border-zinc-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
            <div class="flex items-center gap-2">
                <h2 class="text-base font-semibold text-zinc-900">Pending Requests</h2>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-100">
                <thead class="bg-zinc-50/50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Sender ID</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            User / Tenant</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Purpose</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Status</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Date</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($senderIds as $sid)
                        <tr class="group hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                                        <i data-lucide="hash" class="w-4 h-4"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-zinc-900">{{ $sid->sender_id }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900">{{ $sid->user->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $sid->user->tenant->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-zinc-600 line-clamp-1"
                                    title="{{ $sid->purpose }}">{{ $sid->purpose }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $classes = match ($sid->status) {
                                        'active' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'approved' => 'bg-blue-50 text-blue-600 border-blue-100', // Intermediate state
                                        'payment_pending' => 'bg-blue-50 text-blue-600 border-blue-100',
                                        'rejected' => 'bg-rose-50 text-rose-600 border-rose-100',
                                        'pending' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        default => 'bg-gray-50 text-gray-600 border-gray-100',
                                    };
                                    $label = match ($sid->status) {
                                        'payment_pending' => 'Awaiting Payment',
                                        'pending' => 'Processing',
                                        'active' => 'Active',
                                        'rejected' => ($sid->reason === 'Disabled by Administrator') ? 'Disabled' : 'Rejected',
                                        default => ucfirst($sid->status)
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] text-xs font-bold border {{ $classes }}">
                                    @if($sid->status === 'active')
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    @elseif($sid->status === 'pending')
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    @elseif($sid->status === 'payment_pending' || $sid->status === 'approved')
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-bounce"></span>
                                    @elseif($sid->status === 'rejected')
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    @endif
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-zinc-500">{{ $sid->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($sid->status === 'pending')
                                        <button wire:click="approve({{ $sid->id }})"
                                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors focus:outline-none"
                                            title="Approve">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            <span>Approve</span>
                                        </button>

                                        <button @click="showRejectModal = true; $wire.selectedSenderId = {{ $sid->id }}"
                                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors focus:outline-none"
                                            title="Reject">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            <span>Reject</span>
                                        </button>

                                        <button wire:click="checkStatus({{ $sid->id }})" wire:loading.attr="disabled"
                                            class="p-2 text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all focus:outline-none"
                                            title="Check Status with Details">
                                            <i data-lucide="activity" class="w-4 h-4" wire:loading.class="animate-spin"
                                                wire:target="checkStatus({{ $sid->id }})"></i>
                                        </button>
                                    @elseif ($sid->status === 'active')
                                        <button wire:click="checkStatus({{ $sid->id }})" wire:loading.attr="disabled"
                                            class="p-2 text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all focus:outline-none"
                                            title="Check Status">
                                            <i data-lucide="activity" class="w-4 h-4" wire:loading.class="animate-spin"
                                                wire:target="checkStatus({{ $sid->id }})"></i>
                                        </button>

                                        <button wire:click="toggleStatus({{ $sid->id }})"
                                            wire:confirm="Are you sure you want to disable this Sender ID? It will be removed from the user's dropdown."
                                            class="p-2 text-zinc-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all focus:outline-none"
                                            title="Disable">
                                            <i data-lucide="ban" class="w-4 h-4"></i>
                                        </button>
                                    @elseif (($sid->status === 'rejected' || $sid->status === 'disabled') && $sid->reason === 'Disabled by Administrator')
                                        <button wire:click="toggleStatus({{ $sid->id }})"
                                            wire:confirm="Are you sure you want to re-enable this Sender ID?"
                                            class="p-2 text-zinc-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all focus:outline-none"
                                            title="Enable">
                                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                        </button>

                                        <button wire:click="checkStatus({{ $sid->id }})" wire:loading.attr="disabled"
                                            class="p-2 text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all focus:outline-none"
                                            title="Check Status">
                                            <i data-lucide="activity" class="w-4 h-4" wire:loading.class="animate-spin"
                                                wire:target="checkStatus({{ $sid->id }})"></i>
                                        </button>
                                    @endif

                                    <button wire:click="viewDetails({{ $sid->id }})"
                                        class="p-2 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-50 rounded-lg transition-all focus:outline-none"
                                        title="View Details">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>

                                    <button wire:click="delete({{ $sid->id }})"
                                        wire:confirm="Are you sure you want to permanently delete this Sender ID?"
                                        class="p-2 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all focus:outline-none"
                                        title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-zinc-500">No sender ID requests found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($senderIds->hasPages())
            <div class="px-6 py-4 border-t border-zinc-100">
                {{ $senderIds->links() }}
            </div>
        @endif
    </div>

    <!-- Rejection Modal -->
    <template x-teleport="body">
        <div x-show="showRejectModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">

            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm" @click="showRejectModal = false"></div>

                <!-- Modal Content -->
                <div x-show="showRejectModal" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-8"
                    class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl overflow-hidden">

                    <div class="p-8 md:p-12">
                        <div class="flex justify-between items-start mb-8">
                            <div
                                class="w-14 h-14 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600 border border-rose-100">
                                <i data-lucide="slash" class="w-7 h-7"></i>
                            </div>
                            <button @click="showRejectModal = false"
                                class="p-2 text-zinc-400 hover:text-zinc-900 transition-colors">
                                <i data-lucide="x" class="w-6 h-6"></i>
                            </button>
                        </div>

                        <h3 class="text-2xl font-extrabold text-zinc-900 mb-2 tracking-tight">Reject Sender ID</h3>
                        <p class="text-zinc-500 font-medium mb-8">Please provide a reason for rejecting this sender ID.
                            This will be shown to the user.</p>

                        <form wire:submit.prevent="reject($wire.selectedSenderId)" class="space-y-6">
                            <div class="space-y-2">
                                <label for="rejectionReason"
                                    class="text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] ml-1">Reason
                                    for Rejection</label>
                                <div class="relative group">
                                    <div
                                        class="absolute top-4 left-4 flex items-start pointer-events-none text-zinc-400 group-focus-within:text-indigo-brand transition-colors">
                                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                                    </div>
                                    <textarea wire:model="rejectionReason" id="rejectionReason" rows="3"
                                        class="block w-full pl-12 pr-4 py-4 bg-gray-50 border border-zinc-200 rounded-lg text-zinc-900 font-medium placeholder:text-zinc-300 focus:ring-4 focus:ring-indigo-brand/5 focus:border-indigo-brand focus:bg-white transition-all outline-none"
                                        placeholder="e.g. This sender name is restricted or requires further documentation."></textarea>
                                </div>
                                @error('rejectionReason')
                                    <span
                                        class="text-rose-500 text-xs font-bold flex items-center gap-1 ml-1 animate-shake">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="pt-4 flex flex-col md:flex-row gap-3">
                                <button type="submit"
                                    class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-bold py-4 px-6 rounded-[6px] shadow-xl shadow-rose-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2 group/btn">
                                    <span>Reject Request</span>
                                    <i data-lucide="slash" class="w-4 h-4 transition-transform"></i>
                                </button>
                                <button type="button" @click="showRejectModal = false"
                                    class="px-8 py-4 text-zinc-400 font-bold hover:text-zinc-900 transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Status Check Modal -->
    <template x-teleport="body">
        <div x-show="$wire.showStatusModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">

            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm" @click="$wire.showStatusModal = false">
                </div>

                <!-- Modal Content -->
                <div x-show="$wire.showStatusModal" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-8"
                    class="relative w-full max-w-2xl bg-white rounded-xl shadow-2xl overflow-hidden">

                    <div class="p-8 md:p-10">
                        <div class="flex justify-between items-start mb-6">
                            <div
                                class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                                <i data-lucide="activity" class="w-6 h-6"></i>
                            </div>
                            <button @click="$wire.showStatusModal = false"
                                class="p-2 text-zinc-400 hover:text-zinc-900 transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <h3 class="text-xl font-extrabold text-zinc-900 mb-6 tracking-tight">Status Check Details</h3>

                        @if($statusCheckResult)
                            <div class="space-y-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div
                                        class="p-4 rounded-lg border {{ ($statusCheckResult['status'] ?? '') === 'success' ? 'bg-emerald-50 border-emerald-100 text-emerald-700' : 'bg-rose-50 border-rose-100 text-rose-700' }}">
                                        <span
                                            class="text-xs font-bold uppercase tracking-widest block mb-1 opacity-70">Result
                                            Status</span>
                                        <span
                                            class="text-lg font-bold">{{ ucfirst($statusCheckResult['status'] ?? 'Unknown') }}</span>
                                    </div>
                                    <div class="p-4 rounded-lg bg-zinc-50 border border-zinc-100">
                                        <span
                                            class="text-xs font-bold text-zinc-400 uppercase tracking-widest block mb-1">Mapped
                                            Status</span>
                                        <span
                                            class="text-lg font-bold text-zinc-900">{{ ucfirst($statusCheckResult['mapped_status'] ?? 'N/A') }}</span>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-sm font-bold text-zinc-900 mb-2">Raw API Response</h4>
                                    <div class="bg-zinc-900 rounded-lg p-4 overflow-x-auto">
                                        <pre
                                            class="text-xs text-zinc-300 font-mono">@json($statusCheckResult['raw'] ?? $statusCheckResult, JSON_PRETTY_PRINT)</pre>
                                    </div>
                                </div>

                                @if(isset($statusCheckResult['raw']['summary']))
                                    <div class="bg-zinc-50 rounded-lg p-4 border border-zinc-100">
                                        <h4 class="text-xs font-bold text-zinc-400 uppercase tracking-widest mb-3">Summary Data
                                        </h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                            @foreach($statusCheckResult['raw']['summary'] as $key => $val)
                                                @if(is_string($val) || is_numeric($val))
                                                    <div class="flex flex-col">
                                                        <span
                                                            class="text-zinc-500 text-xs capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                                        <span class="font-medium text-zinc-900">{{ $val }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="py-8 text-center text-zinc-400 italic">No status data available.</div>
                        @endif

                        <div class="mt-8">
                            <button @click="$wire.showStatusModal = false"
                                class="w-full py-3 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-bold rounded-lg transition-colors">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Details Modal -->
    <template x-teleport="body">
        <div x-show="$wire.showDetailsModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">

            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm" @click="$wire.showDetailsModal = false">
                </div>

                <!-- Modal Content -->
                <div x-show="$wire.showDetailsModal" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-8"
                    class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl overflow-hidden">

                    <div class="p-8 md:p-10">
                        <div class="flex justify-between items-start mb-6">
                            <div
                                class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                                <i data-lucide="info" class="w-6 h-6"></i>
                            </div>
                            <button @click="$wire.showDetailsModal = false"
                                class="p-2 text-zinc-400 hover:text-zinc-900 transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <h3 class="text-xl font-extrabold text-zinc-900 mb-6 tracking-tight">Request Details</h3>

                        @if($viewingSenderId)
                            <div class="space-y-4">
                                <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-100">
                                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-widest block mb-1">User
                                        / Tenant</span>
                                    <span
                                        class="text-sm font-bold text-zinc-900 block">{{ $viewingSenderId->user->name }}</span>
                                    <span
                                        class="text-xs text-zinc-500">{{ $viewingSenderId->user->tenant->name ?? 'N/A' }}</span>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-100">
                                        <span
                                            class="text-xs font-bold text-zinc-400 uppercase tracking-widest block mb-1">Sender
                                            Name</span>
                                        <span
                                            class="text-lg font-bold text-zinc-900">{{ $viewingSenderId->sender_id }}</span>
                                    </div>
                                    <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-100">
                                        <span
                                            class="text-xs font-bold text-zinc-400 uppercase tracking-widest block mb-1">Status</span>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($viewingSenderId->status) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-100">
                                    <span
                                        class="text-xs font-bold text-zinc-400 uppercase tracking-widest block mb-1">Purpose</span>
                                    <p class="text-zinc-600 text-sm leading-relaxed">{{ $viewingSenderId->purpose }}</p>
                                </div>

                                @if($viewingSenderId->status === 'rejected' && $viewingSenderId->reason)
                                    <div class="bg-rose-50 p-4 rounded-lg border border-rose-100">
                                        <span
                                            class="text-xs font-bold text-rose-400 uppercase tracking-widest block mb-1">Rejection
                                            Reason</span>
                                        <p class="text-rose-600 text-sm italic">{{ $viewingSenderId->reason }}</p>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between text-xs text-zinc-400 mt-2">
                                    <span>Requested: {{ $viewingSenderId->created_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        @else
                            <div class="py-8 text-center text-zinc-400 italic">No details loaded.</div>
                        @endif

                        <div class="mt-8">
                            <button @click="$wire.showDetailsModal = false"
                                class="w-full py-3 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-bold rounded-lg transition-colors">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('livewire:initialized', () => {
            lucide.createIcons();
            Livewire.on('close-modal', () => {
                // Handled by Alpine
            });
        });
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
    </script>
</div>