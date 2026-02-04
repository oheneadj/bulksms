<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('groups') }}"
                    class="text-[10px] font-bold text-zinc-400 hover:text-indigo-brand uppercase tracking-widest transition-colors">Groups</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-zinc-300"></i>
                <span
                    class="text-[10px] font-bold text-indigo-brand uppercase tracking-widest">{{ $group->name }}</span>
            </div>
            <h1 class="text-3xl font-extrabold text-zinc-900 tracking-tight">{{ $group->name }}</h1>
            <p class="text-zinc-500 font-medium mt-1">{{ $group->description ?? 'Manage contacts in this group.' }}</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="$set('showAddModal', true)"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/20">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Add Contacts
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-zinc-500">Total Contacts</p>
                    <p class="text-2xl font-bold text-zinc-900">{{ $contacts->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contacts List -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="font-bold text-zinc-900">Group Members</h2>
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-4 w-4 text-zinc-400"></i>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text"
                    class="block w-full pl-10 pr-3 py-2 border border-zinc-200 rounded-lg text-sm bg-zinc-50 focus:ring-indigo-500 focus:border-indigo-500 placeholder-zinc-400"
                    placeholder="Search contacts..." />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50/50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Phone</th>
                        <th class="px-6 py-3">Added On</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($contacts as $contact)
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-zinc-900">
                                {{ $contact->name }}
                            </td>
                            <td class="px-6 py-4 font-mono text-zinc-600">{{ $contact->phone }}</td>
                            <td class="px-6 py-4 text-zinc-500">{{ $contact->updated_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <button wire:click="removeContact({{ $contact->id }})"
                                    wire:confirm="Remove this contact from the group?"
                                    class="text-rose-600 hover:text-rose-700 font-bold text-xs">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-zinc-500">
                                No contacts in this group yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contacts->hasPages())
            <div class="px-6 py-4 border-t border-zinc-100 bg-zinc-50/50">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>

    <!-- Add Contacts Modal -->
    @if($showAddModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden"
                @click.away="$wire.set('showAddModal', false)">
                <div class="px-6 py-4 border-b border-zinc-100 flex justify-between items-center bg-zinc-50/50">
                    <h3 class="font-bold text-zinc-900">Add Contacts to Group</h3>
                    <button wire:click="$set('showAddModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <input wire:model.live.debounce.300ms="searchContactsToAdd" type="text"
                            class="w-full px-4 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Search existing contacts..." autofocus>
                    </div>

                    <div class="max-h-60 overflow-y-auto space-y-2 mb-6 border border-zinc-100 rounded-lg p-2">
                        @forelse ($availableContacts as $contact)
                            <label class="flex items-center gap-3 p-2 hover:bg-zinc-50 rounded-lg cursor-pointer">
                                <input type="checkbox" wire:model="selectedContacts" value="{{ $contact->id }}"
                                    class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <p class="text-sm font-bold text-zinc-900">{{ $contact->first_name }}
                                        {{ $contact->last_name }}</p>
                                    <p class="text-xs text-zinc-500 font-mono">{{ $contact->phone }}</p>
                                </div>
                            </label>
                        @empty
                            <p class="text-center text-sm text-zinc-500 py-4">No matching contacts found.</p>
                        @endforelse
                    </div>

                    <div class="flex gap-3">
                        <button wire:click="$set('showAddModal', false)"
                            class="flex-1 px-4 py-2 border border-zinc-200 rounded-lg text-zinc-700 font-bold hover:bg-zinc-50">Cancel</button>
                        <button wire:click="addContacts"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 disabled:opacity-50"
                            @if(empty($selectedContacts)) disabled @endif>
                            Add Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => lucide.createIcons());
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
        document.addEventListener('livewire:updated', () => lucide.createIcons());
    </script>
</div>