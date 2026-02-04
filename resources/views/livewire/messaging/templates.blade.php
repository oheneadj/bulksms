<div class="space-y-8" x-data="{ showModal: false, editing: false }" @open-modal.window="if($event.detail.name === 'template-modal') { showModal = true; editing = true; }" @close-modal.window="showModal = false; editing = false;">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('dashboard') }}"
                    class="text-[10px] font-bold text-zinc-400 hover:text-indigo-brand uppercase tracking-widest transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-zinc-300"></i>
                <span class="text-[10px] font-bold text-indigo-brand uppercase tracking-widest">Messaging</span>
            </div>
            <h2 class="text-3xl font-extrabold text-zinc-900 tracking-tight leading-tight">Message Templates</h2>
            <p class="text-zinc-500 text-sm mt-1 font-medium">Create and manage reusable message formats for your campaigns.</p>
        </div>

        <button @click="showModal = true; editing = false; $wire.resetForm()"
            class="bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-3 px-6 rounded-[6px] shadow-xl shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2 group">
            <i data-lucide="plus" class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300"></i>
            Create Template
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm overflow-hidden flex flex-col">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-zinc-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
            <div class="flex items-center gap-2">
                <h2 class="text-base font-semibold text-zinc-900">All Templates</h2>
                <span class="px-2.5 py-0.5 rounded-full bg-zinc-100 text-zinc-600 text-xs font-medium">{{ $templates->total() }} templates</span>
            </div>

            <div class="relative max-w-xs">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search templates..."
                    class="pl-9 pr-4 py-2 text-sm w-full bg-zinc-50 border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900">
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-100">
                <thead class="bg-zinc-50/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Template Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Content Preview</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Last Updated</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($templates as $template)
                        <tr class="group hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-zinc-900">{{ $template->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-zinc-500 line-clamp-1 max-w-xs">{{ $template->body }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-zinc-500">{{ $template->updated_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $template->id }})"
                                        class="p-2 text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200 group/edit"
                                        title="Edit Template">
                                        <i data-lucide="pencil" class="w-4 h-4 transition-transform group-hover/edit:scale-110"></i>
                                    </button>
                                    <button wire:confirm="Are you sure you want to delete this template?"
                                        wire:click="delete({{ $template->id }})"
                                        class="p-2 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all duration-200 group/delete"
                                        title="Delete Template">
                                        <i data-lucide="trash-2" class="w-4 h-4 transition-transform group-hover/delete:scale-110"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-16 h-16 bg-zinc-50 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="layout-template" class="h-8 w-8 text-zinc-300"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-zinc-900 mb-1">No templates yet</h3>
                                    <p class="text-sm text-zinc-500 mb-6">Create templates to save time when sending recurring messages.</p>
                                    <button @click="showModal = true; editing = false; $wire.resetForm()"
                                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                        Create your first template
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templates->hasPages())
            <div class="px-6 py-4 border-t border-zinc-100">
                {{ $templates->links() }}
            </div>
        @endif
    </div>

    <!-- Modal -->
    <template x-teleport="body">
        <div x-show="showModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">

            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm" @click="showModal = false"></div>

                <!-- Modal Content -->
                <div x-show="showModal" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-8"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-8"
                    class="relative w-full max-w-2xl bg-white rounded-xl shadow-2xl overflow-hidden">

                    <div class="p-8 md:p-12">
                        <div class="flex justify-between items-start mb-8">
                            <div class="w-14 h-14 rounded-xl bg-indigo-brand/10 flex items-center justify-center text-indigo-brand">
                                <i data-lucide="layout-template" class="w-7 h-7"></i>
                            </div>
                            <button @click="showModal = false" class="p-2 text-zinc-400 hover:text-zinc-900 transition-colors">
                                <i data-lucide="x" class="w-6 h-6"></i>
                            </button>
                        </div>

                        <h3 class="text-2xl font-extrabold text-zinc-900 mb-2 tracking-tight" x-text="editing ? 'Edit Template' : 'Create Template'"></h3>
                        <p class="text-zinc-500 font-medium mb-8">Define a reusable message format with dynamic placeholders.</p>

                        <form wire:submit="save" class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] ml-1">Template Name</label>
                                <input type="text" wire:model="name"
                                    class="block w-full px-4 py-4 bg-gray-50 border border-zinc-200 rounded-lg text-zinc-900 font-bold focus:ring-4 focus:ring-indigo-brand/5 focus:border-indigo-brand focus:bg-white transition-all outline-none"
                                    placeholder="e.g. Welcome Message">
                                @error('name') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] ml-1">Message Body</label>
                                <div class="relative">
                                    <textarea wire:model="body" rows="6"
                                        class="block w-full px-4 py-4 bg-gray-50 border border-zinc-200 rounded-lg text-zinc-900 font-medium focus:ring-4 focus:ring-indigo-brand/5 focus:border-indigo-brand focus:bg-white transition-all outline-none"
                                        placeholder="Hello @{{first_name}}, welcome to SLMOBBIN!"></textarea>
                                </div>
                                @error('body') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                                
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="text-[10px] font-bold text-zinc-400 uppercase mr-2">Available Tags:</span>
                                    <button type="button" @click="$wire.body += ' @{{title}}'" class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 text-[10px] font-bold rounded">@{{title}}</button>
                                    <button type="button" @click="$wire.body += ' @{{first_name}}'" class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 text-[10px] font-bold rounded">@{{first_name}}</button>
                                    <button type="button" @click="$wire.body += ' @{{surname}}'" class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 text-[10px] font-bold rounded">@{{surname}}</button>
                                    <button type="button" @click="$wire.body += ' @{{phone}}'" class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 text-[10px] font-bold rounded">@{{phone}}</button>
                                </div>
                            </div>

                            <div class="pt-4 flex flex-col md:flex-row gap-3">
                                <button type="submit"
                                    class="flex-1 bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-4 px-6 rounded-[6px] shadow-xl shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2 group/btn">
                                    <span x-text="editing ? 'Update Template' : 'Save Template'"></span>
                                    <i data-lucide="check" class="w-4 h-4 transition-transform group-hover/btn:scale-110"></i>
                                </button>
                                <button type="button" @click="showModal = false"
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

    <script>
        document.addEventListener('livewire:initialized', () => {
            lucide.createIcons();
        });
        
        // Re-initialize icons after Livewire updates
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
        document.addEventListener('livewire:load', () => lucide.createIcons());
    </script>
</div>
