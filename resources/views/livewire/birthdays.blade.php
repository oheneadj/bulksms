<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-zinc-900 tracking-tight">Birthdays</h1>
            <p class="text-zinc-500 font-medium mt-1">Automated birthday wishes and upcoming dates.</p>
        </div>
    </div>

    <!-- Settings Card -->
    <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
        <div class="flex flex-col md:flex-row gap-6 justify-between items-start md:items-center">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-pink-50 rounded-xl text-pink-600">
                    <i data-lucide="cake" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-zinc-900">Automation Settings</h2>
                    <p class="text-xs text-zinc-500 font-medium">Configure automatic birthday wishes.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="$toggle('isEnabled')"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $isEnabled ? 'bg-indigo-600' : 'bg-zinc-200' }}"
                    role="switch" aria-checked="{{ $isEnabled }}">
                    <span class="sr-only">Use setting</span>
                    <span aria-hidden="true"
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
                <span class="text-sm font-bold {{ $isEnabled ? 'text-indigo-600' : 'text-zinc-400' }}">
                    {{ $isEnabled ? 'Enabled' : 'Disabled' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-zinc-50">
            <div class="space-y-2">
                <label class="text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Sender ID</label>
                <select wire:model="senderId"
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-lg px-4 py-2.5 focus:border-indigo-500 focus:ring-0 text-sm font-bold">
                    <option value="">Select Sender ID</option>
                    @foreach($senderIds as $sid)
                        <option value="{{ $sid->name }}">{{ $sid->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Template</label>
                <select wire:model="templateId"
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-lg px-4 py-2.5 focus:border-indigo-500 focus:ring-0 text-sm font-bold">
                    <option value="">Select Template</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Send Time</label>
                <input type="time" wire:model="sendTime"
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-lg px-4 py-2.5 focus:border-indigo-500 focus:ring-0 text-sm font-bold">
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button wire:click="saveSettings"
                class="bg-zinc-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider px-6 py-2.5 rounded-lg transition-all">
                Save Settings
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Today's Birthdays -->
        <div class="space-y-4">
            <h3 class="text-sm font-extrabold text-zinc-900 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                Today's Birthdays
            </h3>

            @forelse($todaysBirthdays as $contact)
                <div
                    class="bg-white border border-zinc-200 shadow-sm rounded-xl p-5 flex items-center justify-between group hover:border-pink-200 transition-all">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold">
                            {{ substr($contact->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-zinc-900">{{ $contact->name }}</h4>
                            <p class="text-xs text-zinc-500">{{ $contact->phone }}</p>
                        </div>
                    </div>

                    @if($contact->has_sent_today)
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold uppercase tracking-wider">
                            <i data-lucide="check-circle" class="w-3 h-3"></i> Sent
                        </span>
                    @else
                        <button wire:click="sendWish({{ $contact->id }})"
                            class="bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-lg shadow-pink-500/20 transition-all active:scale-95 flex items-center gap-2">
                            <i data-lucide="send" class="w-3 h-3"></i>
                            Send Wish
                        </button>
                    @endif
                </div>
            @empty
                <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-8 text-center text-zinc-400">
                    <p class="text-sm font-medium">No birthdays today.</p>
                </div>
            @endforelse
        </div>

        <!-- Upcoming Birthdays -->
        <div class="space-y-4">
            <h3 class="text-sm font-extrabold text-zinc-900 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Upcoming (30 days)
            </h3>

            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden divide-y divide-zinc-100">
                @forelse($upcomingBirthdays as $contact)
                    <div class="p-4 flex items-center justify-between hover:bg-zinc-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-zinc-100 flex items-center justify-center text-zinc-500 font-bold text-xs">
                                {{ $contact->dob->format('d') }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-zinc-900">{{ $contact->name }}</h4>
                                <p class="text-[10px] text-zinc-500 uppercase tracking-wider font-bold">
                                    {{ $contact->dob->format('M d') }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            @php
                                $bday = $contact->dob->copy()->setYear(now()->year);
                                if ($bday->isPast())
                                    $bday->addYear();
                                $days = $bday->diffInDays(now());
                             @endphp
                            <span class="text-xs font-bold text-indigo-600">in {{ $days }} days</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-zinc-400">
                        <p class="text-sm font-medium">No upcoming birthdays.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>