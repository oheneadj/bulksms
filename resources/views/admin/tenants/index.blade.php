<x-layouts::admin title="Tenant Management">
    <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden">
        <div class="px-8 py-6 border-b border-zinc-100 flex justify-between items-center">
            <h3 class="font-bold text-zinc-900">All Tenants</h3>
            <!-- Search could go here -->
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50/50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-8 py-4">Name</th>
                        <th class="px-8 py-4">Domain/Slug</th>
                        <th class="px-8 py-4">Credits</th>
                        <th class="px-8 py-4">Status</th>
                        <th class="px-8 py-4 text-right">Created</th>
                        <th class="px-8 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($tenants as $tenant)
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-8 py-4 font-bold text-zinc-900">{{ $tenant->name }}</td>
                            <td class="px-8 py-4 font-mono text-xs text-zinc-500">{{ $tenant->slug ?? 'N/A' }}</td>
                            <td class="px-8 py-4 font-medium">{{ number_format($tenant->sms_credits) }}</td>
                            <td class="px-8 py-4">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800">
                                    Active
                                </span>
                            </td>
                            <td class="px-8 py-4 text-right text-zinc-500">{{ $tenant->created_at->format('M d, Y') }}</td>
                            <td class="px-8 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 text-[10px]"
                                    x-data="{ showCreditModal: false }">
                                    <a href="{{ route('admin.tenants.impersonate', $tenant->id) }}"
                                        class="flex items-center gap-1 font-bold text-indigo-600 hover:text-indigo-800 border border-indigo-200 px-2 py-1 rounded bg-indigo-50/50 hover:bg-indigo-50 transition-colors">
                                        <i data-lucide="log-in" class="w-3 h-3"></i> Login
                                    </a>

                                    <button
                                        @click="$dispatch('open-credit-modal', { tenantId: {{ $tenant->id }}, tenantName: '{{ addslashes($tenant->name) }}' })"
                                        class="flex items-center gap-1 font-bold text-zinc-600 hover:text-zinc-800 border border-zinc-200 px-2 py-1 rounded bg-white hover:bg-zinc-50 transition-colors">
                                        <i data-lucide="coins" class="w-3 h-3"></i> Credits
                                    </button>

                                    @if($tenant->status === 'active')
                                        <form action="{{ route('admin.tenants.suspend', $tenant->id) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Suspend this tenant?');">
                                            @csrf
                                            <button type="submit"
                                                class="flex items-center gap-1 font-bold text-amber-600 hover:text-amber-800 border border-amber-200 px-2 py-1 rounded bg-amber-50/50 hover:bg-amber-50 transition-colors">
                                                <i data-lucide="pause-circle" class="w-3 h-3"></i> Suspend
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.tenants.reactivate', $tenant->id) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Reactivate this tenant?');">
                                            @csrf
                                            <button type="submit"
                                                class="flex items-center gap-1 font-bold text-emerald-600 hover:text-emerald-800 border border-emerald-200 px-2 py-1 rounded bg-emerald-50/50 hover:bg-emerald-50 transition-colors">
                                                <i data-lucide="play-circle" class="w-3 h-3"></i> Active
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.tenants.destroy', $tenant->id) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this tenant? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="flex items-center gap-1 font-bold text-gray-400 hover:text-rose-600 border border-gray-200 hover:border-rose-200 px-2 py-1 rounded bg-gray-50 hover:bg-rose-50 transition-colors">
                                            <i data-lucide="trash-2" class="w-3 h-3"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Adjust Credits Modal (Using Alpine) -->
        <div x-data="{ open: false, tenantId: null, tenantName: '', amount: '', reason: '' }"
            @open-credit-modal.window="open = true; tenantId = $event.detail.tenantId; tenantName = $event.detail.tenantName; amount = ''; reason = ''"
            x-show="open" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm"
            x-transition>

            <div @click.outside="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-100 flex justify-between items-center bg-zinc-50/50">
                    <h3 class="font-bold text-zinc-900">Adjust Credits: <span x-text="tenantName"></span></h3>
                    <button @click="open = false" class="text-zinc-400 hover:text-zinc-600"><i data-lucide="x"
                            class="w-5 h-5"></i></button>
                </div>

                <form :action="`/admin/tenants/${tenantId}/credits`" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Amount
                            (Positive to Add, Negative to Deduct)</label>
                        <input type="number" name="amount" x-model="amount" required
                            class="w-full border-zinc-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g. 500 or -100">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Reason</label>
                        <input type="text" name="reason" x-model="reason" required
                            class="w-full border-zinc-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g. Bonus, Refund correction">
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="open = false"
                            class="flex-1 px-4 py-2 border border-zinc-200 rounded-lg text-zinc-600 font-bold hover:bg-zinc-50">Cancel</button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-indigo-brand text-white rounded-lg font-bold hover:bg-indigo-700 shadow-md shadow-indigo-500/20">Submit
                            Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::admin>