<div>
    <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden">
        <!-- Header & Controls -->
        <div class="px-6 py-6 border-b border-zinc-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-zinc-900 text-lg">All Tenants</h3>
                <p class="text-sm text-zinc-500">Manage your system tenants.</p>
            </div>

            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <!-- Status Filter -->
                <select wire:model.live="filterStatus"
                    class="bg-zinc-50 border border-zinc-200 text-zinc-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>

                <!-- Search -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-zinc-400"></i>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="bg-zinc-50 border border-zinc-200 text-zinc-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5"
                        placeholder="Search tenants...">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto relative" wire:loading.class="opacity-50">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50/50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th scope="col"
                            class="px-6 py-4 cursor-pointer hover:bg-zinc-100 transition-colors whitespace-nowrap"
                            wire:click="sortBy('name')">
                            <div class="flex items-center gap-1">
                                Name
                                @if($sortField === 'name')
                                    <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="w-3 h-3"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col"
                            class="px-6 py-4 cursor-pointer hover:bg-zinc-100 transition-colors whitespace-nowrap"
                            wire:click="sortBy('slug')">
                            <div class="flex items-center gap-1">
                                Domain/Slug
                                @if($sortField === 'slug')
                                    <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="w-3 h-3"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col"
                            class="px-6 py-4 cursor-pointer hover:bg-zinc-100 transition-colors whitespace-nowrap"
                            wire:click="sortBy('sms_credits')">
                            <div class="flex items-center gap-1">
                                Credits
                                @if($sortField === 'sms_credits')
                                    <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="w-3 h-3"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Status</th>
                        <th scope="col"
                            class="px-6 py-4 text-right cursor-pointer hover:bg-zinc-100 transition-colors whitespace-nowrap"
                            wire:click="sortBy('created_at')">
                            <div class="flex items-center justify-end gap-1">
                                Created
                                @if($sortField === 'created_at')
                                    <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="w-3 h-3"></i>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-4 text-right whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($tenants as $tenant)
                        <tr class="hover:bg-zinc-50/50 transition-colors" wire:key="tenant-{{ $tenant->id }}">
                            <td class="px-6 py-4 font-bold text-zinc-900 whitespace-nowrap">{{ $tenant->name }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-zinc-500 whitespace-nowrap">
                                {{ $tenant->slug ?? 'N/A' }}</td>
                            <td class="px-6 py-4 font-medium whitespace-nowrap">{{ number_format($tenant->sms_credits) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($tenant->status === 'active')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">Active</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">Suspended</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-zinc-500 whitespace-nowrap">
                                {{ $tenant->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.tenants.impersonate', $tenant->id) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-colors text-xs font-bold whitespace-nowrap"
                                        title="Login as Admin">
                                        <i data-lucide="log-in" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                        Login
                                    </a>

                                    <button
                                        wire:click="openCreditModal({{ $tenant->id }}, '{{ addslashes($tenant->name) }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-zinc-100 text-zinc-600 hover:bg-zinc-200 rounded-lg transition-colors text-xs font-bold whitespace-nowrap"
                                        title="Adjust Credits">
                                        <i data-lucide="coins" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                        Credits
                                    </button>

                                    @if($tenant->status === 'active')
                                        <button
                                            wire:click="confirmSuspend({{ $tenant->id }}, '{{ addslashes($tenant->name) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg transition-colors text-xs font-bold whitespace-nowrap"
                                            title="Suspend">
                                            <i data-lucide="pause-circle" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                            Suspend
                                        </button>
                                    @else
                                        <button
                                            wire:click="confirmReactivate({{ $tenant->id }}, '{{ addslashes($tenant->name) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg transition-colors text-xs font-bold whitespace-nowrap"
                                            title="Reactivate">
                                            <i data-lucide="play-circle" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                            Active
                                        </button>
                                    @endif

                                    <button wire:click="confirmDelete({{ $tenant->id }}, '{{ addslashes($tenant->name) }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg transition-colors text-xs font-bold whitespace-nowrap"
                                        title="Delete">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-zinc-500">
                                No tenants found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div class="px-6 py-4 border-t border-zinc-100 bg-zinc-50/50">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>

    <!-- Modals (Teleported if possible, but here placed at root) -->

    <!-- Suspend Confirmation -->
    @if($confirmingSuspension)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all"
                @click.away="$wire.set('confirmingSuspension', false)">
                <div class="p-6 text-center">
                    <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-triangle" class="w-6 h-6 text-amber-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Suspend Tenant?</h3>
                    <p class="text-sm text-zinc-500 mb-6">
                        Are you sure you want to suspend <strong>{{ $targetTenantName }}</strong>?
                    </p>
                    <div class="flex gap-3">
                        <button wire:click="$set('confirmingSuspension', false)"
                            class="flex-1 px-4 py-2 border border-zinc-200 rounded-lg text-zinc-700 font-bold hover:bg-zinc-50 transition-colors">Cancel</button>
                        <button wire:click="suspend"
                            class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg font-bold hover:bg-amber-700 transition-colors shadow-lg shadow-amber-500/20">Suspend</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Reactivate Confirmation -->
    @if($confirmingReactivation)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all"
                @click.away="$wire.set('confirmingReactivation', false)">
                <div class="p-6 text-center">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check-circle" class="w-6 h-6 text-emerald-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Reactivate Tenant?</h3>
                    <p class="text-sm text-zinc-500 mb-6">
                        Confirm reactivation for <strong>{{ $targetTenantName }}</strong>.
                    </p>
                    <div class="flex gap-3">
                        <button wire:click="$set('confirmingReactivation', false)"
                            class="flex-1 px-4 py-2 border border-zinc-200 rounded-lg text-zinc-700 font-bold hover:bg-zinc-50 transition-colors">Cancel</button>
                        <button wire:click="reactivate"
                            class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-500/20">Reactivate</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation -->
    @if($confirmingDeletion)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all"
                @click.away="$wire.set('confirmingDeletion', false)">
                <div class="p-6 text-center">
                    <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="trash-2" class="w-6 h-6 text-rose-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Delete Tenant?</h3>
                    <p class="text-sm text-zinc-500 mb-6">
                        This action cannot be undone.
                    </p>
                    <div class="flex gap-3">
                        <button wire:click="$set('confirmingDeletion', false)"
                            class="flex-1 px-4 py-2 border border-zinc-200 rounded-lg text-zinc-700 font-bold hover:bg-zinc-50 transition-colors">Cancel</button>
                        <button wire:click="delete"
                            class="flex-1 px-4 py-2 bg-rose-600 text-white rounded-lg font-bold hover:bg-rose-700 transition-colors shadow-lg shadow-rose-500/20">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Credits Modal -->
    @if($showingCreditModal)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all"
                @click.away="$wire.set('showingCreditModal', false)">
                <div class="px-6 py-4 border-b border-zinc-100 flex justify-between items-center bg-zinc-50/50">
                    <h3 class="font-bold text-zinc-900">Adjust Credits: {{ $targetTenantName }}</h3>
                    <button wire:click="$set('showingCreditModal', false)"
                        class="text-zinc-400 hover:text-zinc-600 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Amount</label>
                        <input type="number" wire:model="creditAmount"
                            class="w-full border-zinc-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5 shadow-sm"
                            placeholder="e.g. 500 or -100">
                        @error('creditAmount') <span
                        class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                        <p class="text-xs text-zinc-400 mt-1.5">Positive to Add, Negative to Deduct.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Reason</label>
                        <input type="text" wire:model="creditReason"
                            class="w-full border-zinc-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 p-2.5 shadow-sm"
                            placeholder="e.g. Bonus">
                        @error('creditReason') <span
                        class="text-xs text-rose-500 font-medium block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-3 pt-3">
                        <button wire:click="$set('showingCreditModal', false)"
                            class="flex-1 px-4 py-2.5 border border-zinc-200 rounded-lg text-zinc-600 font-bold hover:bg-zinc-50 transition-colors">Cancel</button>
                        <button wire:click="adjustCredits"
                            class="flex-1 px-4 py-2.5 bg-indigo-brand text-white rounded-lg font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 transition-colors">Submit
                            Adjustment</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            lucide.createIcons();
        });
        document.addEventListener('livewire:navigated', () => {
            lucide.createIcons();
        });
        document.addEventListener('livewire:updated', () => {
            // Small timeout to ensure DOM update is fully painted
            setTimeout(() => {
                lucide.createIcons();
            }, 50);
        });
    </script>
</div>