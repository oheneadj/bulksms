<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-12">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Groups</h1>
            <p class="text-sm text-zinc-500">Manage your contact groups</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex-1 w-full space-y-1.5">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-4 w-4 text-zinc-400"></i>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        class="block w-full pl-10 pr-3 py-2.5 border border-zinc-200 rounded-lg leading-5 bg-zinc-50 font-medium placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-sm transition-all"
                        placeholder="Find groups by name..." />
                </div>
            </div>
            <div class="w-40 md:w-auto space-y-1.5">

                <select wire:change="sortBy($event.target.value)"
                    class="w-full p-3 bg-zinc-50 border border-zinc-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm tracking-wider  px-4">
                    <option value="created_at">Date Created</option>
                    <option value="name">Alpha (Name)</option>
                    <option value="contacts_count">Group Size</option>
                </select>
            </div>
            <x-button wire:click="create" icon="plus">Create Group</x-button>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div x-data="{ show: @entangle('showCreateModal').live }" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/50 backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden" @click.away="show = false">
            <div class="p-6 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900">Create Group</h3>
                <button @click="show = false" class="text-zinc-400 hover:text-zinc-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form wire:submit="save" class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                    <input wire:model="name" type="text" placeholder="e.g. VIP Customers"
                        class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900 @error('name') border-red-500 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea wire:model="description" rows="3" placeholder="e.g. High value clients for special offers"
                        class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900 @error('description') border-red-500 @enderror"></textarea>
                    @error('description') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" @click="show = false"
                        class="px-6 py-3 text-sm font-bold text-gray-700 hover:text-gray-900 bg-white border border-zinc-200 rounded-[6px] hover:bg-zinc-50 transition-all shadow-sm">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-3 text-sm font-bold text-white bg-[#612fff] hover:bg-indigo-brand-hover rounded-[6px] shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.99]">
                        Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-data="{ show: @entangle('showEditModal').live }" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/50 backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden" @click.away="show = false">
            <div class="p-6 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900">Edit Group</h3>
                <button @click="show = false" class="text-zinc-400 hover:text-zinc-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form wire:submit="update" class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                    <input wire:model="name" type="text" placeholder="e.g. VIP Customers"
                        class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900 @error('name') border-red-500 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea wire:model="description" rows="3" placeholder="e.g. High value clients for special offers"
                        class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900 @error('description') border-red-500 @enderror"></textarea>
                    @error('description') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" @click="show = false"
                        class="px-6 py-3 text-sm font-bold text-gray-700 hover:text-gray-900 bg-white border border-zinc-200 rounded-[6px] hover:bg-zinc-50 transition-all shadow-sm">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-3 text-sm font-bold text-white bg-[#612fff] hover:bg-indigo-brand-hover rounded-[6px] shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.99]">
                        Update Group
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-6">
        @forelse ($groups as $group)
            <div
                class="bg-white rounded-xl border border-zinc-200 shadow-200/50 hover:shadow-md transition-all p-6 flex flex-col h-full group relative">
                <div class="flex items-start justify-between mb-6">
                    <div
                        class="p-3 bg-indigo-50 rounded-xl text-indigo-600 ring-1 ring-indigo-500/10 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                        <i data-lucide="folder" class="h-6 w-6"></i>
                    </div>

                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <a href="{{ route('groups.show', $group->id) }}"
                            class="p-2 text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200"
                            title="Manage Contacts">
                            <i data-lucide="users" class="w-4 h-4"></i>
                        </a>
                        <button wire:click="edit({{ $group->id }})"
                            class="p-2 text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200"
                            title="Edit Group">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                        <button
                            wire:confirm="Are you sure you want to delete this group? All contacts will remain but will no longer be part of this group."
                            wire:click="delete({{ $group->id }})"
                            class="p-2 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all duration-200"
                            title="Delete Group">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="flex-1 mb-6 cursor-pointer" onclick="window.location='{{ route('groups.show', $group->id) }}'">
                    <h3 class="text-lg font-bold text-zinc-900 mb-2 tracking-tight hover:text-indigo-600 transition-colors">
                        {{ $group->name }}</h3>
                    <p class="text-sm text-zinc-500 leading-relaxed line-clamp-2">
                        {{ $group->description ?? 'No description provided.' }}
                    </p>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-zinc-50">
                    <a href="{{ route('groups.show', $group->id) }}"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] text-xs font-bold bg-zinc-100 text-zinc-600 group-hover:bg-indigo-50 group-hover:text-indigo-700 transition-colors">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        {{ $group->contacts_count }} Contacts
                    </a>
                    <span class="text-xs font-medium text-zinc-400">Created
                        {{ $group->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-xl border border-zinc-200 shadow-sm p-16 text-center">
                    <div class="w-16 h-16 bg-zinc-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="folder-plus" class="h-8 w-8 text-zinc-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">No groups yet</h3>
                    <p class="text-sm text-zinc-500 max-w-sm mx-auto mb-8">Create your first group to organize contacts and
                        send targeted campaigns.</p>
                    <button wire:click="create"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-[#612fff] hover:bg-indigo-brand-hover rounded-[6px] shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98]">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Create Group
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $groups->links() }}
    </div>
</div>