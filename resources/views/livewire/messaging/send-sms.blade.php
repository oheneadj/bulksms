<div class="space-y-8" x-data="{ 
    message: $wire.entangle('message'),
    get charCount() { return this.message.length },
    get segmentCount() { return Math.ceil(this.charCount / 160) || 0 }
}">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('dashboard') }}"
                    class="text-[10px] font-bold text-zinc-400 hover:text-indigo-brand uppercase tracking-widest transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-zinc-300"></i>
                <span class="text-[10px] font-bold text-indigo-brand uppercase tracking-widest">Messaging</span>
            </div>
            <h2 class="text-3xl font-extrabold text-zinc-900 tracking-tight leading-tight">Send SMS</h2>
            <p class="text-zinc-500 text-sm mt-1 font-medium">Compose and send high-conversion SMS campaigns to your
                audience.</p>
        </div>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Message Composition -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-8 md:p-10">
                <form wire:submit="sendSms" class="space-y-8">
                    <!-- Target Selection -->
                    <div class="inline-flex p-1 bg-zinc-100 rounded-lg mb-6">
                        <button type="button" wire:click="$set('targetType', 'individual')"
                            class="px-4 py-2 text-xs font-bold rounded-md transition-all {{ $targetType === 'individual' ? 'bg-white text-indigo-600 shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}">
                            Individual Numbers
                        </button>
                        <button type="button" wire:click="$set('targetType', 'group')"
                            class="px-4 py-2 text-xs font-bold rounded-md transition-all {{ $targetType === 'group' ? 'bg-white text-indigo-600 shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}">
                            Contact Groups
                        </button>
                        <button type="button" wire:click="$set('targetType', 'all')"
                            class="px-4 py-2 text-xs font-bold rounded-md transition-all {{ $targetType === 'all' ? 'bg-white text-indigo-600 shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}">
                            All Contacts
                        </button>
                    </div>

                    <!-- Recipients (Individual) -->
                    <div class="space-y-3" x-show="$wire.targetType === 'individual'">
                        <label for="recipients"
                            class="block text-sm font-semibold text-zinc-900 mb-1">Recipients</label>
                        <div class="relative group">
                            <div
                                class="absolute top-4 left-4 text-zinc-400 group-focus-within:text-indigo-600 transition-colors">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <textarea wire:model="recipients" id="recipients" rows="3"
                                class="block w-full pl-12 pr-4 py-4 bg-gray-50 border border-zinc-200 rounded-lg text-zinc-900 font-medium placeholder:text-zinc-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white transition-all outline-none resize-none"
                                placeholder="Enter phone numbers separated by commas..."></textarea>
                        </div>
                        @error('recipients')
                            <p class="text-rose-600 text-xs font-medium flex items-center gap-1 mt-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="text-xs text-zinc-500 font-medium flex items-center gap-1.5 ml-1">
                            <i data-lucide="info" class="w-3.5 h-3.5 text-zinc-400"></i>
                            Format: +447123456789, +447987654321
                        </p>
                    </div>

                    <!-- Contact Groups (Selection) -->
                    <div class="space-y-3" x-show="$wire.targetType === 'group'" x-cloak>
                        <label for="selectedGroupId" class="block text-sm font-semibold text-zinc-900 mb-1">Select
                            Contact Group</label>
                        <div class="relative group">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400 group-focus-within:text-indigo-600 transition-colors">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <select wire:model="selectedGroupId" id="selectedGroupId"
                                class="block w-full pl-12 pr-4 py-4 bg-gray-50 border border-zinc-200 rounded-lg text-zinc-900 font-bold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white transition-all outline-none appearance-none">
                                <option value="">-- Choose a Group --</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->contacts_count }}
                                        contacts)</option>
                                @endforeach
                            </select>
                            <div
                                class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-zinc-400">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </div>
                        </div>
                        @error('selectedGroupId')
                            <p class="text-rose-600 text-xs font-medium flex items-center gap-1 mt-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- All Contacts Info -->
                    <div class="space-y-3" x-show="$wire.targetType === 'all'" x-cloak>
                        <div class="p-6 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center gap-4">
                            <div class="bg-indigo-100 p-3 rounded-full text-indigo-600">
                                <i data-lucide="users" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-zinc-900">{{ __('Broadcasting to all contacts') }}
                                </h4>
                                <p class="text-xs text-zinc-500 font-medium">
                                    {{ __('Your message will be sent to all :count contacts in your directory.', ['count' => auth()->user()->tenant->contacts()->count()]) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label for="message" class="block text-sm font-semibold text-zinc-900">Message
                                Content</label>

                            <div class="flex items-center gap-2">
                                <label class="text-[10px] font-extrabold text-zinc-400 uppercase tracking-widest">Select
                                    Template:</label>
                                <select wire:model.live="selectedTemplateId"
                                    class="text-xs font-bold text-indigo-600 bg-indigo-50 border-none rounded-lg py-1 px-3 focus:ring-0 cursor-pointer">
                                    <option value="">-- No Template --</option>
                                    @foreach (\App\Models\MessageTemplate::latest('created_at')->get() as $tpl)
                                        <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="relative group">
                            <!-- Manual Entry -->
                            <div x-show="!$wire.selectedTemplateId">
                                <textarea wire:model.live="message" id="message" rows="8"
                                    class="block w-full p-6 bg-gray-50 border border-zinc-200 rounded-lg text-zinc-900 font-medium placeholder:text-zinc-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white transition-all outline-none resize-none"
                                    placeholder="Type your message here or select a template..."></textarea>

                                <div class="flex flex-wrap gap-2 mt-3 ml-1">
                                    <span class="text-[10px] font-bold text-zinc-400 uppercase mr-1 flex items-center">
                                        <i data-lucide="tag" class="w-3 h-3 mr-1"></i> Quick Tags:
                                    </span>
                                    <button type="button" @click="message += ' @{{title}}'"
                                        class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 text-[10px] font-extrabold rounded transition-colors select-none">@{{title}}</button>
                                    <button type="button" @click="message += ' @{{first_name}}'"
                                        class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 text-[10px] font-extrabold rounded transition-colors select-none">@{{first_name}}</button>
                                    <button type="button" @click="message += ' @{{surname}}'"
                                        class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 text-[10px] font-extrabold rounded transition-colors select-none">@{{surname}}</button>
                                    <button type="button" @click="message += ' @{{phone}}'"
                                        class="px-2 py-1 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 text-[10px] font-extrabold rounded transition-colors select-none">@{{phone}}</button>
                                </div>
                            </div>

                            <!-- Template Selected State -->
                            <div x-show="$wire.selectedTemplateId" x-cloak
                                class="p-8 bg-indigo-50/50 border border-indigo-100 rounded-xl flex flex-col items-center justify-center text-center">
                                <div
                                    class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mb-4 text-indigo-600">
                                    <i data-lucide="file-check" class="w-6 h-6"></i>
                                </div>
                                <h3 class="text-sm font-bold text-zinc-900">Template Active</h3>
                                <p class="text-xs text-zinc-500 mt-1 max-w-xs mx-auto">The content of this message is
                                    controlled by the selected template.</p>
                                <button type="button" wire:click="$set('selectedTemplateId', '')"
                                    class="mt-4 text-xs font-bold text-indigo-600 hover:underline">Switch to manual
                                    entry</button>
                            </div>

                            <!-- Character Count Floating Badge -->
                            <div
                                class="absolute bottom-4 right-4 flex items-center gap-4 bg-white/80 backdrop-blur px-4 py-2 rounded-lg border border-zinc-100 shadow-sm">
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] font-extrabold text-zinc-400 uppercase tracking-tighter"
                                        x-text="charCount">0</span>
                                    <span class="text-[8px] font-bold text-zinc-300 uppercase leading-none">Chars</span>
                                </div>
                                <div class="w-px h-6 bg-zinc-100"></div>
                                <div class="flex flex-col items-center">
                                    <span
                                        class="text-[10px] font-extrabold text-indigo-brand uppercase tracking-tighter"
                                        x-text="segmentCount">0</span>
                                    <span class="text-[8px] font-bold text-zinc-300 uppercase leading-none">Parts</span>
                                </div>
                            </div>
                        </div>
                        @error('message')
                            <span class="text-rose-500 text-xs font-bold flex items-center gap-1 ml-1">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Scheduling Options -->
                    <div class="bg-indigo-50/50 rounded-xl p-6 border border-indigo-100">
                        <label class="flex items-center justify-between mb-4 cursor-pointer group/toggle">
                            <div class="flex items-center gap-3">
                                <div
                                    class="bg-indigo-100 p-2 rounded-lg text-indigo-600 group-hover/toggle:bg-indigo-600 group-hover/toggle:text-white transition-colors">
                                    <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-zinc-900">Schedule for later</span>
                                    <span class="text-xs text-zinc-500 font-medium">Send this campaign automatically
                                        at
                                        a specific time</span>
                                </div>
                            </div>
                            <!-- Toggle Switch -->
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" wire:model.live="schedule" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-zinc-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600">
                                </div>
                            </div>
                        </label>

                        <div x-show="$wire.schedule" x-transition.opacity class="pt-2">
                            <label for="scheduledAt" class="block text-sm font-semibold text-zinc-900 mb-2">Delivery
                                Time</label>
                            <div class="relative">
                                <input type="datetime-local" wire:model="scheduledAt" id="scheduledAt"
                                    class="block w-full pl-10 pr-4 py-3 bg-white border border-zinc-200 rounded-lg text-zinc-900 font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                </div>
                            </div>
                            @error('scheduledAt')
                                <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit"
                            class="w-full bg-[#612fff] hover:bg-indigo-brand-hover text-white font-bold py-4 px-8 rounded-[6px] shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-3">
                            <span class="text-base"
                                x-text="$wire.schedule ? 'Schedule Campaign' : 'Send Message Now'">Send Message
                                Now</span>
                            <i data-lucide="send" class="w-5 h-5"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar / Settings -->
        <div class="space-y-8">
            <!-- Sender ID Selection -->
            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
                <h3 class="text-sm font-semibold text-zinc-900 mb-4">Sender Identity</h3>
                <div class="space-y-3">
                    @forelse ($senderIds as $sender)
                        @php
                            $isActive = $sender->status === 'active';
                            $statusColor = match ($sender->status) {
                                'active' => 'emerald',
                                'rejected' => 'rose',
                                default => 'amber',
                            };
                            $statusLabel = match ($sender->status) {
                                'active' => 'Verified',
                                'rejected' => 'Rejected',
                                'payment_pending' => 'Pending Approval',
                                'payment_required' => 'Payment Required',
                                default => ucfirst($sender->status),
                            };
                            $icon = match ($sender->status) {
                                'active' => 'check-circle-2',
                                'rejected' => 'x-circle',
                                default => 'clock',
                            };
                        @endphp
                        <label class="relative flex items-center gap-3 p-3 rounded-xl border border-zinc-200 transition-all 
                                    {{ $isActive ? 'cursor-pointer hover:bg-zinc-50 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-600' : 'opacity-60 cursor-not-allowed bg-zinc-50' }} 
                                    group">
                            <input type="radio" wire:model="sender_id" value="{{ $sender->sender_id }}" class="hidden peer"
                                {{ !$isActive ? 'disabled' : '' }}>
                            <div
                                class="w-9 h-9 rounded-lg bg-zinc-100 flex items-center justify-center text-zinc-400 {{ $isActive ? 'group-hover:text-indigo-600' : '' }} transition-colors peer-checked:bg-indigo-600 peer-checked:text-white">
                                <i data-lucide="{{ $isActive ? 'hash' : 'lock' }}" class="w-4 h-4"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-zinc-900">{{ $sender->sender_id }}</span>
                                <span class="text-[10px] font-medium text-{{ $statusColor }}-600 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $statusColor }}-500"></span>
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            @if($isActive)
                                <div class="ml-auto opacity-0 peer-checked:opacity-100 transition-opacity">
                                    <i data-lucide="check-circle-2" class="w-5 h-5 text-indigo-600 fill-indigo-50"></i>
                                </div>
                            @endif
                        </label>
                    @empty
                        <div class="p-6 text-center border-2 border-dashed border-zinc-100 rounded-xl">
                            <p class="text-xs font-medium text-zinc-400 mb-4">No approved Sender IDs</p>
                            <a href="{{ route('messaging.sender-ids') }}"
                                class="text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline">Request
                                one</a>
                        </div>
                    @endforelse
                </div>
                @error('sender_id')
                    <p class="text-rose-600 text-xs font-medium flex items-center gap-1 mt-4">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Sending Tips -->
            <div class="bg-zinc-900 rounded-xl p-8 relative overflow-hidden group">
                <div class="relative z-10">
                    <h3
                        class="text-xs font-extrabold text-white uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                        <i data-lucide="lightbulb" class="text-yellow-400 w-4 h-4"></i>
                        Sending Tips
                    </h3>
                    <ul class="space-y-4">
                        <li class="flex gap-3">
                            <div class="size-1.5 rounded-full bg-indigo-400 mt-1.5 shrink-0"></div>
                            <p class="text-xs text-zinc-400 font-medium leading-relaxed">Keep messages under 160 chars
                                to avoid multi-part costs.</p>
                        </li>
                        <li class="flex gap-3">
                            <div class="size-1.5 rounded-full bg-indigo-400 mt-1.5 shrink-0"></div>
                            <p class="text-xs text-zinc-400 font-medium leading-relaxed">Always include an opt-out for
                                marketing campaigns.</p>
                        </li>
                    </ul>
                </div>
                <div
                    class="absolute -bottom-6 -right-6 opacity-5 text-white transition-transform group-hover:scale-110 duration-700">
                    <i data-lucide="info" class="w-32 h-32"></i>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => lucide.createIcons());
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
    </script>
</div>