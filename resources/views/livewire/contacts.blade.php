<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Contacts</h1>
            <p class="text-sm text-zinc-500 font-medium mt-1">Manage your audience and segments.</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search contacts..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm bg-zinc-50 border border-zinc-200 rounded-lg focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
            </div>
            <div x-data="{ open: false }" class="relative w-40 md:w-auto">
                <button @click="open = !open"
                    class="w-full md:w-auto px-5 py-2.5 text-sm font-bold text-zinc-700 bg-white border border-zinc-200 rounded-lg hover:bg-zinc-50 transition-all flex items-center justify-center gap-2 shadow-sm">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Filters
                    @php $activeFilters = ($filterGroup ? 1 : 0) + ($filterMonth ? 1 : 0); @endphp
                    @if($activeFilters > 0)
                        <span
                            class="inline-flex items-center justify-center bg-indigo-600 text-white text-[10px] w-5 h-5 rounded-full border-2 border-white shadow-sm font-bold">
                            {{ $activeFilters }}
                        </span>
                    @endif
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform"
                        :class="{ 'rotate-180': open }"></i>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 bg-white rounded-xl shadow-2xl border border-zinc-200 z-50 overflow-hidden"
                    style="display: none;">

                    <div class="p-4 w-full space-y-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest ml-1">Group
                                Segment</label>
                            <select wire:model.live="filterGroup"
                                class="w-full text-sm bg-zinc-50 border border-zinc-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium py-2 px-3">
                                <option value="">All Groups</option>
                                @foreach($allGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest ml-1">Birthday
                                Month</label>
                            <select wire:model.live="filterMonth"
                                class="w-full text-sm bg-zinc-50 border border-zinc-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium py-2 px-3">
                                <option value="">Any Month</option>
                                @php $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']; @endphp
                                @foreach($months as $index => $month)
                                    <option value="{{ $index + 1 }}">{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest ml-1">Status</label>
                            <select wire:model.live="filterStatus"
                                class="w-full text-sm bg-zinc-50 border border-zinc-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium py-2 px-3">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="unsubscribed">Unsubscribed</option>
                            </select>
                        </div>
                    </div>

                    @if($filterGroup || $filterMonth || $filterStatus)
                        <div class="p-3 bg-zinc-50 border-t border-zinc-100 flex justify-center">
                            <button wire:click="resetFilters"
                                class="text-xs font-bold text-rose-500 hover:text-rose-600 uppercase tracking-wider flex items-center gap-1.5 py-1 px-3 rounded-md hover:bg-rose-50 transition-all">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                Reset Filters
                            </button>
                        </div>
                    @endif
                </div>
            </div>
            <x-button wire:click="$set('showImportModal', true)" variant="outline"
                icon="arrow-up-tray">Import</x-button>
            <x-button wire:click="create" icon="plus" class="bg-indigo-700 shadow-lg shadow-indigo-500/20">Add
                Contact</x-button>
        </div>
    </div>

    <!-- Toolbar: Search & Filters -->


    <!-- Bulk Actions Bar -->
    @if(!empty($selectedContacts))
        <div
            class="fixed bottom-8 left-1/2 -translate-x-1/2 z-40 bg-zinc-900 text-white rounded-2xl shadow-2xl border border-white/10 px-6 py-4 flex items-center gap-6 animate-in slide-in-from-bottom-8 duration-500">
            <div class="flex items-center gap-3 pr-6 border-r border-white/10">
                <span
                    class="bg-indigo-600 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ count($selectedContacts) }}</span>
                <span class="text-sm font-bold tracking-tight">Selected</span>
            </div>

            <div class="flex items-center gap-4">
                <button wire:click="bulkMessage"
                    class="flex items-center gap-2 text-xs font-bold hover:text-indigo-400 transition-colors uppercase tracking-widest">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Message
                </button>

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-2 text-xs font-bold hover:text-indigo-400 transition-colors uppercase tracking-widest">
                        <i data-lucide="folder-plus" class="w-4 h-4"></i>
                        Move to
                    </button>
                    <div x-show="open" @click.away="open = false"
                        class="absolute bottom-full mb-4 left-1/2 -translate-x-1/2 bg-white text-zinc-900 rounded-xl shadow-2xl border border-zinc-200 w-48 overflow-hidden py-1">
                        <button wire:click="bulkMoveToGroup('')"
                            class="w-full text-left px-4 py-2 text-xs font-bold hover:bg-zinc-50 uppercase tracking-wider text-zinc-500">No
                            Group</button>
                        @foreach($allGroups as $group)
                            <button wire:click="bulkMoveToGroup({{ $group->id }})"
                                class="w-full text-left px-4 py-2 text-xs font-bold hover:bg-zinc-50 uppercase tracking-wider">{{ $group->name }}</button>
                        @endforeach
                    </div>
                </div>

                <button wire:click="bulkExport"
                    class="flex items-center gap-2 text-xs font-bold hover:text-indigo-400 transition-colors uppercase tracking-widest">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Export
                </button>

                <div class="w-px h-4 bg-white/10 mx-2"></div>

                <button wire:click="bulkDelete" wire:confirm="Delete {{ count($selectedContacts) }} contacts?"
                    class="flex items-center gap-2 text-xs font-bold text-rose-500 hover:text-rose-400 transition-colors uppercase tracking-widest">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Delete
                </button>
            </div>

            <button wire:click="$set('selectedContacts', [])"
                class="ml-6 p-2 hover:bg-white/10 rounded-lg transition-colors text-zinc-500 hover:text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- Import Modal -->
    <div x-data="{ show: @entangle('showImportModal').live }" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/50 backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden" @click.away="show = false">
            <div class="p-6 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900">Import Contacts</h3>
                <button @click="show = false" class="text-zinc-400 hover:text-zinc-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div x-data="{ dragging: false }"
                    @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                    @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
                    :class="{ 'border-indigo-500 bg-indigo-50': dragging, 'border-zinc-200 bg-zinc-50': !dragging }"
                    class="relative border-2 border-dashed rounded-xl p-8 text-center transition-colors">

                    <input type="file" wire:model="csvFile" x-ref="fileInput"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                    <div class="flex flex-col items-center gap-2 pointer-events-none" wire:loading.remove
                        wire:target="csvFile">
                        <div class="p-3 bg-white rounded-lg shadow-sm">
                            <i data-lucide="upload-cloud" class="w-6 h-6 text-indigo-600"></i>
                        </div>
                        <p class="text-sm font-medium text-zinc-900">
                            <span class="text-indigo-600">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-xs text-zinc-500">CSV files only (max. 10MB)</p>
                    </div>

                    <div class="flex flex-col items-center gap-2" wire:loading wire:target="csvFile">
                        <div class="p-3 bg-white rounded-lg shadow-sm">
                            <i data-lucide="loader" class="w-6 h-6 text-indigo-600 animate-spin"></i>
                        </div>
                        <p class="text-sm font-medium text-zinc-900">Uploading...</p>
                    </div>
                </div>

                @if ($csvFile)
                    <div
                        class="flex items-center justify-between gap-3 p-3 bg-indigo-50 rounded-lg border border-indigo-100">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <i data-lucide="file-text" class="w-5 h-5 text-indigo-600 shrink-0"></i>
                            <span
                                class="text-sm font-medium text-indigo-900 truncate">{{ $csvFile->getClientOriginalName() }}</span>
                        </div>
                        <button wire:click="$set('csvFile', null)"
                            class="text-indigo-400 hover:text-indigo-600 transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @error('csvFile')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="bg-zinc-50 p-4 rounded-lg text-xs text-zinc-600 space-y-2">
                    <div class="flex items-center justify-between font-medium text-zinc-900 mb-2">
                        <span>CSV Format Requirements:</span>
                        <button wire:click="downloadTemplate"
                            class="flex items-center gap-1.5 text-indigo-600 hover:text-indigo-700 transition-colors font-bold uppercase tracking-wider">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            Download Template
                        </button>
                    </div>
                    <div class="space-y-1">
                    </div>
                    <div class="space-y-1">
                        <p>• Required columns: <span class="font-mono bg-zinc-200 px-1 rounded">first_name</span>, <span
                                class="font-mono bg-zinc-200 px-1 rounded">phone</span></p>
                        <p>• Optional columns: <span class="font-mono bg-zinc-200 px-1 rounded">title</span>, <span
                                class="font-mono bg-zinc-200 px-1 rounded">surname</span>, <span
                                class="font-mono bg-zinc-200 px-1 rounded">email</span>, <span
                                class="font-mono bg-zinc-200 px-1 rounded">dob</span>, <span
                                class="font-mono bg-zinc-200 px-1 rounded">group</span></p>
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-zinc-100 bg-zinc-50 flex justify-end gap-3">
                <button type="button" @click="show = false"
                    class="px-4 py-2 text-sm font-medium text-zinc-700 hover:text-zinc-900 bg-white border border-zinc-200 rounded-lg hover:bg-zinc-50 transition-colors">
                    Cancel
                </button>
                <button wire:click="import" wire:loading.attr="disabled"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-brand hover:bg-indigo-brand-hover rounded-lg shadow-sm shadow-indigo-500/20 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="import">Import Contacts</span>
                    <span wire:loading wire:target="import">Importing...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div x-data="{ show: @entangle('showContactModal').live }" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/50 backdrop-blur-sm"
        style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden" @click.away="show = false">
            <div class="p-6 border-b border-zinc-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-zinc-900">{{ $editingContact ? 'Edit Contact' : 'Add Contact' }}
                </h3>
                <button @click="show = false" class="text-zinc-400 hover:text-zinc-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form wire:submit="{{ $editingContact ? 'update' : 'save' }}" class="p-6 space-y-6">

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 mb-2 font-bold uppercase tracking-wider text-[10px]">Title</label>
                    <select wire:model="title"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium font-gray-900">
                        <option value="">Select Title (Optional)</option>
                        <option value="Mr">Mr</option>
                        <option value="Mrs">Mrs</option>
                        <option value="Ms">Ms</option>
                        <option value="Dr">Dr</option>
                        <option value="Prof">Prof</option>
                        <option value="Rev">Rev</option>
                    </select>
                    @error('title') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2 font-bold uppercase tracking-wider text-[10px]">First
                            Name</label>
                        <input wire:model="first_name" type="text" placeholder="e.g. John"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium font-gray-900">
                        @error('first_name') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2 font-bold uppercase tracking-wider text-[10px]">Surname</label>
                        <input wire:model="surname" type="text" placeholder="e.g. Doe"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium font-gray-900">
                        @error('surname') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 mb-2 font-bold uppercase tracking-wider text-[10px]">Phone</label>
                    <input wire:model="phone" type="tel" placeholder="e.g. +233241234567"
                        x-on:input="$wire.phone = $wire.phone.replace(/[^0-9+]/g, '')"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium font-gray-900">
                    @error('phone') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2 font-bold uppercase tracking-wider text-[10px]">Email</label>
                        <input wire:model="email" type="email" placeholder="e.g. john@example.com"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium font-gray-900">
                        @error('email') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2 font-bold uppercase tracking-wider text-[10px]">Date
                            of Birth</label>
                        <input wire:model="dob" type="date"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium font-gray-900">
                        @error('dob') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 mb-2 font-bold uppercase tracking-wider text-[10px]">Group</label>
                    <select wire:model="group_id"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-medium font-gray-900">
                        <option value="">Select a group (optional)</option>
                        @foreach($allGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" @click="show = false"
                        class="px-6 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 rounded-lg border border-gray-200 transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-brand hover:bg-indigo-700 rounded-lg shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98]">
                        {{ $editingContact ? 'Update' : 'Save' }} Contact
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-100">
                <thead class="bg-zinc-50/50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left w-4">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model.live="selectAll"
                                    class="w-4 h-4 rounded border-zinc-300 text-[#612fff] focus:ring-indigo-500/20">
                            </label>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left">
                            <button wire:click="sortBy('name')"
                                class="flex items-center gap-1.5 text-xs font-bold text-zinc-500 uppercase tracking-widest hover:text-indigo-600 transition-colors">
                                Name
                                @if($sortField === 'name')
                                    <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="w-4 h-4"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-4 text-left">
                            <button wire:click="sortBy('phone')"
                                class="flex items-center gap-1.5 text-xs font-bold text-zinc-500 uppercase tracking-widest hover:text-indigo-600 transition-colors">
                                Phone
                                @if($sortField === 'phone')
                                    <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="w-4 h-4"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-widest">
                            Birthday
                        </th>
                        <th scope="col" class="px-6 py-4 text-left">
                            <button wire:click="sortBy('group_id')"
                                class="flex items-center gap-1.5 text-xs font-bold text-zinc-500 uppercase tracking-widest hover:text-indigo-600 transition-colors">
                                Group
                                @if($sortField === 'group_id')
                                    <i data-lucide="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                        class="w-4 h-4"></i>
                                @endif
                            </button>
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-right text-xs font-bold text-zinc-500 uppercase tracking-widest">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white">
                    @forelse ($contacts as $contact)
                        <tr class="hover:bg-zinc-50/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model.live="selectedContacts" value="{{ $contact->id }}"
                                        class="w-4 h-4 rounded border-zinc-300 text-[#612fff] focus:ring-indigo-500/20">
                                </label>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <img class="h-9 w-9 rounded-lg bg-zinc-100 object-cover border border-zinc-100 shadow-sm"
                                        src="https://ui-avatars.com/api/?name={{ urlencode($contact->name) }}&color=612fff&background=eef2ff&bold=true"
                                        alt="{{ $contact->name }}">
                                    <div>
                                        <div class="text-sm font-extrabold text-zinc-900 tracking-tight">
                                            {{ $contact->name }}
                                        </div>
                                        <div class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">
                                            Added {{ $contact->created_at->format('M d, Y') }}
                                            @if($contact->is_unsubscribed)
                                                <span
                                                    class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-600 uppercase tracking-wider">Unsubscribed</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm text-zinc-700 font-mono font-bold">{{ $contact->phone }}</span>
                                    @if($contact->email)
                                        <span class="text-xs text-zinc-400 font-medium">{{ $contact->email }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 rounded-full {{ $contact->dob ? 'bg-indigo-400' : 'bg-zinc-200' }}">
                                    </div>
                                    <span class="text-sm font-bold text-zinc-600">
                                        {{ $contact->dob ? $contact->dob->format('M d, Y') : '—' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($contact->group)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase tracking-wider">
                                        {{ $contact->group->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-zinc-300 font-medium">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1 px-2">
                                    <button wire:click="messageContact({{ $contact->id }})"
                                        class="p-2 text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                        title="Send Message">
                                        <i data-lucide="send" class="w-4 text-indigo-500 h-4"></i>
                                    </button>
                                    <button wire:click="edit({{ $contact->id }})"
                                        class="p-2 text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                        title="Edit">
                                        <i data-lucide="pencil" class="w-4 text-indigo-500 h-4"></i>
                                    </button>

                                    @if($contact->is_unsubscribed)
                                        <button wire:click="reactivate({{ $contact->id }})"
                                            class="p-2 text-zinc-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all"
                                            title="Reactivate">
                                            <i data-lucide="check-circle" class="w-4 text-emerald-500 h-4"></i>
                                        </button>
                                    @else
                                        <button
                                            wire:confirm="Unsubscribe this contact? They will be excluded from future campaigns."
                                            wire:click="unsubscribe({{ $contact->id }})"
                                            class="p-2 text-zinc-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
                                            title="Unsubscribe">
                                            <i data-lucide="ban" class="w-4 text-amber-500 h-4"></i>
                                        </button>
                                    @endif

                                    <button wire:confirm="Are you sure?" wire:click="delete({{ $contact->id }})"
                                        class="p-2 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all"
                                        title="Delete">
                                        <i data-lucide="trash-2" class="w-4 text-red-500 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-16 h-16 bg-zinc-50 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="users" class="h-8 w-8 text-zinc-300"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-zinc-900 mb-1">No contacts found</h3>
                                    <p class="text-sm text-zinc-500 mb-6 font-medium">Try adjusting your filters or add a
                                        new contact.</p>
                                    <button wire:click="create"
                                        class="px-6 py-2 bg-indigo-brand text-white text-sm font-bold rounded-lg shadow-lg shadow-indigo-500/20">
                                        Add Contact
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-zinc-100">
            {{ $contacts->links() }}
        </div>
    </div>
</div>