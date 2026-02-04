<div>
    <x-slot name="title">Packages & Inventory</x-slot>

    <!-- Inventory Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">System Balance</h3>
            <div class="flex items-baseline gap-2">
                <span
                    class="text-3xl font-extrabold {{ $inventory->balance < 1000 ? 'text-rose-600' : 'text-zinc-900' }}">
                    {{ number_format($inventory->balance) }}
                </span>
                <span class="text-xs font-bold text-zinc-400">Credits</span>
            </div>
            @if($inventory->balance < 1000)
                <p class="text-xs text-rose-500 font-bold mt-2">Low inventory!</p>
            @endif
        </div>

        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Total Purchased</h3>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-zinc-900">
                    {{ number_format($inventory->total_purchased) }}
                </span>
                <span class="text-xs font-bold text-zinc-400">Lifetime</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Actions</h3>
                <p class="text-sm text-zinc-600">Purchase more credits from your provider, then update here.</p>
            </div>
            <button wire:click="openRestockModal"
                class="mt-4 w-full bg-zinc-900 hover:bg-zinc-800 text-white font-bold py-2 px-4 rounded-lg transition-all">
                Restock Inventory
            </button>
        </div>
    </div>

    <!-- Restock Modal -->
    @if($showRestockModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
                <div class="p-6 border-b border-zinc-100 flex justify-between items-center">
                    <h3 class="text-lg font-extrabold text-zinc-900">Restock Inventory</h3>
                    <button wire:click="$set('showRestockModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-zinc-500 uppercase mb-1">Amount to Add</label>
                        <input type="number" wire:model="restockAmount"
                            class="w-full bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-2 font-bold text-zinc-900 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('restockAmount') <span class="text-xs text-rose-600 font-bold">{{ $message }}</span>
                        @enderror
                    </div>
                    <button wire:click="restockInventory"
                        class="w-full bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-3 rounded-lg shadow-lg shadow-indigo-500/20">
                        Confirm Restock
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Packages Section -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-zinc-900">Credit Packages</h2>
            <p class="text-sm text-zinc-500">Manage the packages available to your users.</p>
        </div>
        <button wire:click="create"
            class="bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Create Package
        </button>
    </div>

    <div class="bg-white border border-zinc-200 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left">
            <thead class="bg-zinc-50 border-b border-zinc-200">
                <tr>
                    <th class="px-6 py-4 text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Package Name
                    </th>
                    <th class="px-6 py-4 text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Credits</th>
                    <th class="px-6 py-4 text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Unit Price</th>
                    <th class="px-6 py-4 text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Total Price</th>
                    <th class="px-6 py-4 text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-extrabold text-zinc-500 uppercase tracking-wider text-right">
                        Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($packages as $package)
                    <tr class="hover:bg-zinc-50/50 transition-colors">
                        <td class="px-6 py-4 text-sm font-bold text-zinc-900">{{ $package->name }}</td>
                        <td class="px-6 py-4 text-sm font-bold text-zinc-600">{{ number_format($package->credits) }}</td>
                        <td class="px-6 py-4 text-sm font-mono text-zinc-500">{{ $package->unit_price }}</td>
                        <td class="px-6 py-4 text-sm font-bold text-emerald-600">{{ number_format($package->price, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-bold {{ $package->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-500' }}">
                                {{ $package->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button wire:click="edit({{ $package->id }})"
                                    class="p-1.5 text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </button>
                                <button wire:click="deletePackage({{ $package->id }})"
                                    class="p-1.5 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-zinc-500">
                            No packages found. Create one to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $packages->links() }}
    </div>

    <!-- Package Modal -->
    @if($showPackageModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
                <div class="p-6 border-b border-zinc-100 flex justify-between items-center">
                    <h3 class="text-lg font-extrabold text-zinc-900">
                        {{ $editingPackageId ? 'Edit Package' : 'Create Package' }}</h3>
                    <button wire:click="$set('showPackageModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-500 uppercase mb-1">Package Name</label>
                        <input type="text" wire:model="name" placeholder="e.g. Starter Bundle"
                            class="w-full bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-2 text-sm font-bold text-zinc-900 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name') <span class="text-xs text-rose-600 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-500 uppercase mb-1">Credits</label>
                            <input type="number" wire:model.live="credits"
                                class="w-full bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-2 text-sm font-bold text-zinc-900 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('credits') <span class="text-xs text-rose-600 font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-500 uppercase mb-1">Unit Price</label>
                            <input type="number" step="0.0001" wire:model.live="unit_price"
                                class="w-full bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-2 text-sm font-bold text-zinc-900 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('unit_price') <span class="text-xs text-rose-600 font-bold">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="p-3 bg-indigo-50 rounded-lg border border-indigo-100 flex justify-between items-center">
                        <span class="text-sm font-bold text-indigo-900">Total Price:</span>
                        <span
                            class="text-xl font-extrabold text-indigo-700">{{ number_format((float) $credits * (float) $unit_price, 2) }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-500 uppercase mb-1">Description (Optional)</label>
                        <textarea wire:model="description" rows="3"
                            class="w-full bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-2 text-sm text-zinc-700 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_active" id="is_active"
                            class="rounded border-zinc-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <label for="is_active" class="text-sm font-bold text-zinc-700">Active (Visible to users)</label>
                    </div>

                    <button wire:click="savePackage"
                        class="w-full mt-2 bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-3 rounded-lg shadow-lg shadow-indigo-500/20">
                        Save Package
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>