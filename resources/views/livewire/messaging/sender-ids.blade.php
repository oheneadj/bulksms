<div class="space-y-8" x-data="{ showModal: false, showPaymentModal: @entangle('showPaymentModal') }"
    @close-modal.window="showModal = false">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('dashboard') }}"
                    class="text-[10px] font-bold text-zinc-400 hover:text-indigo-brand uppercase tracking-widest transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-zinc-300"></i>
                <span class="text-[10px] font-bold text-indigo-brand uppercase tracking-widest">Messaging</span>
            </div>
            <h2 class="text-3xl font-extrabold text-zinc-900 tracking-tight leading-tight">Sender IDs</h2>
            <p class="text-zinc-500 text-sm mt-1 font-medium">Manage the identities used when sending messages to your
                customers.</p>
        </div>

        <button @click="showModal = true"
            class="bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-3 px-6 rounded-[6px] shadow-xl shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2 group">
            <i data-lucide="plus" class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300"></i>
            Request New ID
        </button>
    </div>

    <!-- Success Message -->

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm overflow-hidden flex flex-col">
        <!-- Card Header -->
        <div
            class="px-6 py-4 border-b border-zinc-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
            <div class="flex items-center gap-2">
                <h2 class="text-base font-semibold text-zinc-900">All Sender IDs</h2>
                <span
                    class="px-2.5 py-0.5 rounded-full bg-zinc-100 text-zinc-600 text-xs font-medium">{{ $senderIds->count() }}
                    identities</span>
            </div>

            <div class="relative max-w-xs">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                <input type="text" placeholder="Search..."
                    class="pl-9 pr-4 py-2 text-sm w-full bg-zinc-50 border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900">
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-100">
                <thead class="bg-zinc-50/50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Sender Name</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Status</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Requested Date</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($senderIds as $sender)
                        <tr class="group hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                                        <i data-lucide="hash" class="w-4 h-4"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-zinc-900">{{ $sender->sender_id }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClasses = match ($sender->status) {
                                        'active' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'approved' => 'bg-blue-50 text-blue-600 border-blue-100', // Legacy/Intermediate
                                        'payment_pending' => 'bg-blue-50 text-blue-600 border-blue-100',
                                        'rejected' => 'bg-rose-50 text-rose-600 border-rose-100',
                                        'pending' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        default => 'bg-gray-50 text-gray-600 border-gray-100',
                                    };
                                    $label = match ($sender->status) {
                                        'payment_pending' => 'Approved - Pay Now',
                                        'pending' => 'Processing',
                                        'active' => 'Active',
                                        default => ucfirst($sender->status)
                                    };
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] text-xs font-bold border {{ $statusClasses }}">
                                    @if($sender->status === 'active')
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    @elseif($sender->status === 'pending')
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    @elseif($sender->status === 'payment_pending' || $sender->status === 'approved')
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    @elseif($sender->status === 'rejected')
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    @endif
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap relative">
                                <span
                                    class="text-sm text-zinc-500 mb-1 block">{{ $sender->created_at->format('M d, Y') }}</span>
                                @if($sender->status === 'payment_pending')
                                    <button wire:click="initiatePayment({{ $sender->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-600 text-white text-[10px] uppercase font-bold tracking-wider rounded-full shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all transform hover:-translate-y-0.5">
                                        <i data-lucide="credit-card" class="w-3 h-3"></i> Pay to Activate
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @if($sender->status === 'pending')
                                        <button wire:click="refreshStatus({{ $sender->id }})" wire:loading.attr="disabled"
                                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors focus:outline-none"
                                            title="Check Status">
                                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5" wire:loading.class="animate-spin" wire:target="refreshStatus({{ $sender->id }})"></i>
                                            <span>Check Status</span>
                                        </button>
                                    @endif

                                    @if($sender->status === 'rejected')
                                        <div x-data="{ open: false }" class="relative">
                                            <button @click="open = !open" @click.away="open = false" 
                                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors focus:outline-none">
                                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                                <span>Reason</span>
                                            </button>
                                            
                                            <div x-show="open" 
                                                class="absolute right-0 bottom-full mb-2 w-64 p-3 bg-white border border-rose-100 shadow-xl rounded-xl z-20 text-left">
                                                <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider mb-1">Rejection Reason</p>
                                                <p class="text-xs text-zinc-600">{{ $sender->reason }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Actions Menu -->
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" @click.away="open = false"
                                            class="p-1.5 text-zinc-300 hover:text-zinc-600 rounded-lg transition-colors focus:outline-none">
                                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                        </button>

                                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black/5 focus:outline-none py-1 overflow-hidden"
                                            style="display: none;">
                                            
                                            @if($sender->status === 'payment_pending')
                                                <button wire:click="initiatePayment({{ $sender->id }})"
                                                    class="flex items-center w-full px-4 py-2.5 text-sm text-indigo-600 hover:bg-indigo-50 font-medium gap-2 transition-colors">
                                                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                                                    Pay to Activate
                                                </button>
                                            @endif
                                            
                                            <button wire:click="viewDetails({{ $sender->id }})" 
                                                class="flex items-center w-full px-4 py-2.5 text-sm text-zinc-600 hover:bg-zinc-50 gap-2 transition-colors">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                                View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-16 h-16 bg-zinc-50 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="message-square-dashed" class="h-8 w-8 text-zinc-300"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-zinc-900 mb-1">No Sender IDs yet</h3>
                                    <p class="text-sm text-zinc-500 mb-6">Request a custom sender ID to start sending
                                        branded SMS messages.</p>
                                    <button @click="showModal = true"
                                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                        Request New ID
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Request Modal -->
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
                    class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl overflow-hidden">

                    <div class="p-8 md:p-12">
                        <div class="flex justify-between items-start mb-8">
                            <div
                                class="w-14 h-14 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                                <i data-lucide="send" class="w-7 h-7"></i>
                            </div>
                            <button @click="showModal = false"
                                class="p-2 text-zinc-400 hover:text-zinc-900 transition-colors">
                                <i data-lucide="x" class="w-6 h-6"></i>
                            </button>
                        </div>

                        <h3 class="text-2xl font-extrabold text-zinc-900 mb-2 tracking-tight">Request Sender ID</h3>
                        <p class="text-zinc-500 font-medium mb-8">Create a unique identity for your messages. This is
                            how
                            your customers will recognize you.</p>

                        <form wire:submit="requestSenderId" class="space-y-6">
                            <!-- Sender ID Input -->
                            <div class="space-y-2">
                                <label for="senderId"
                                    class="text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] ml-1">Sender
                                    Name</label>
                                <div class="relative group">
                                    <div
                                        class="absolute top-4 left-4 flex items-start pointer-events-none text-zinc-400 group-focus-within:text-indigo-brand transition-colors">
                                        <i data-lucide="hash" class="w-5 h-5"></i>
                                    </div>
                                    <input type="text" wire:model="senderId" id="senderId"
                                        class="block w-full pl-12 pr-4 py-4 bg-zinc-50 border border-zinc-200 rounded-lg text-zinc-900 font-bold placeholder:text-zinc-300 focus:ring-4 focus:ring-indigo-brand/5 focus:border-indigo-brand focus:bg-white transition-all outline-none"
                                        placeholder="e.g. MYBRAND">
                                </div>
                                @error('senderId')
                                    <span
                                        class="text-rose-500 text-xs font-bold flex items-center gap-1 ml-1 animate-shake">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        {{ $message }}
                                    </span>
                                @enderror
                                <p class="text-xs text-zinc-400 ml-1">Max 11 characters. Alphanumeric only.</p>
                            </div>

                            <!-- Purpose Input -->
                            <div class="space-y-2">
                                <label for="purpose"
                                    class="text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] ml-1">Purpose</label>
                                <div class="relative group">
                                    <div
                                        class="absolute top-4 left-4 flex items-start pointer-events-none text-zinc-400 group-focus-within:text-indigo-brand transition-colors">
                                        <i data-lucide="file-text" class="w-5 h-5"></i>
                                    </div>
                                    <textarea wire:model="purpose" id="purpose" rows="3"
                                        class="block w-full pl-12 pr-4 py-4 bg-zinc-50 border border-zinc-200 rounded-lg text-zinc-900 font-medium placeholder:text-zinc-300 focus:ring-4 focus:ring-indigo-brand/5 focus:border-indigo-brand focus:bg-white transition-all outline-none"
                                        placeholder="e.g. To send transaction alerts to customers."></textarea>
                                </div>
                                @error('purpose')
                                    <span
                                        class="text-rose-500 text-xs font-bold flex items-center gap-1 ml-1 animate-shake">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <button type="submit"
                                class="w-full bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-4 px-6 rounded-[6px] shadow-xl shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2 group/btn">
                                <span>Submit Request</span>
                                <i data-lucide="arrow-right"
                                    class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Payment Modal -->
    <template x-teleport="body">
        <div x-show="showPaymentModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">

            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm" @click="showPaymentModal = false"></div>

                <!-- Modal Content -->
                <div x-show="showPaymentModal" x-transition:enter="transition ease-out duration-300"
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
                                <i data-lucide="credit-card" class="w-6 h-6"></i>
                            </div>
                            <button @click="showPaymentModal = false"
                                class="p-2 text-zinc-400 hover:text-zinc-900 transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <h3 class="text-xl font-extrabold text-zinc-900 mb-2 tracking-tight">Complete Payment</h3>
                        <p class="text-zinc-500 font-medium text-sm mb-8">Select a payment method to activate your
                            Sender
                            ID.
                            <br><strong>Amount: GHS
                                {{ number_format(config('bulksms.sender_id_price', 50), 2) }}</strong>
                        </p>

                        <form wire:submit="processPayment" class="space-y-6">
                            <div class="space-y-3">
                                <!-- Paystack -->
                                <label
                                    class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-all hover:bg-zinc-50 {{ $selectedGateway === 'paystack' ? 'border-indigo-600 bg-indigo-50/30 ring-1 ring-indigo-600' : 'border-zinc-200' }}">
                                    <input type="radio" wire:model.live="selectedGateway" value="paystack"
                                        class="sr-only">
                                    <div
                                        class="w-5 h-5 rounded-full border border-zinc-300 flex items-center justify-center {{ $selectedGateway === 'paystack' ? 'border-indigo-600' : '' }}">
                                        @if($selectedGateway === 'paystack')
                                            <div class="w-2.5 h-2.5 bg-indigo-600 rounded-full"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <span class="block text-sm font-bold text-zinc-900">Paystack</span>
                                        <span class="block text-xs text-zinc-500 font-medium">Mobile Money & Card</span>
                                    </div>
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/0/0b/Paystack_Logo.png"
                                        alt="Paystack" class="h-4 opacity-60">
                                </label>

                                <!-- Flutterwave -->
                                <label
                                    class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-all hover:bg-zinc-50 {{ $selectedGateway === 'flutterwave' ? 'border-indigo-600 bg-indigo-50/30 ring-1 ring-indigo-600' : 'border-zinc-200' }}">
                                    <input type="radio" wire:model.live="selectedGateway" value="flutterwave"
                                        class="sr-only">
                                    <div
                                        class="w-5 h-5 rounded-full border border-zinc-300 flex items-center justify-center {{ $selectedGateway === 'flutterwave' ? 'border-indigo-600' : '' }}">
                                        @if($selectedGateway === 'flutterwave')
                                            <div class="w-2.5 h-2.5 bg-indigo-600 rounded-full"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <span class="block text-sm font-bold text-zinc-900">Flutterwave</span>
                                        <span class="block text-xs text-zinc-500 font-medium">Mobile Money & Card</span>
                                    </div>
                                </label>

                                <!-- Stripe -->
                                <label
                                    class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-all hover:bg-zinc-50 {{ $selectedGateway === 'stripe' ? 'border-indigo-600 bg-indigo-50/30 ring-1 ring-indigo-600' : 'border-zinc-200' }}">
                                    <input type="radio" wire:model.live="selectedGateway" value="stripe"
                                        class="sr-only">
                                    <div
                                        class="w-5 h-5 rounded-full border border-zinc-300 flex items-center justify-center {{ $selectedGateway === 'stripe' ? 'border-indigo-600' : '' }}">
                                        @if($selectedGateway === 'stripe')
                                            <div class="w-2.5 h-2.5 bg-indigo-600 rounded-full"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <span class="block text-sm font-bold text-zinc-900">Stripe</span>
                                        <span class="block text-xs text-zinc-500 font-medium">Credit Card</span>
                                    </div>
                                </label>
                            </div>

                            <button type="submit"
                                class="w-full bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-4 px-6 rounded-[6px] shadow-xl shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                                <span>Proceed to Payment</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </button>
                        </form>
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
                <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm" @click="$wire.showDetailsModal = false"></div>

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
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                                <i data-lucide="info" class="w-6 h-6"></i>
                            </div>
                            <button @click="$wire.showDetailsModal = false" class="p-2 text-zinc-400 hover:text-zinc-900 transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <h3 class="text-xl font-extrabold text-zinc-900 mb-6 tracking-tight">Sender ID Details</h3>

                        @if($viewingSenderId)
                            <div class="space-y-4">
                                <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-100">
                                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-widest block mb-1">Sender Name</span>
                                    <span class="text-lg font-bold text-zinc-900">{{ $viewingSenderId->sender_id }}</span>
                                </div>
                                
                                <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-100">
                                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-widest block mb-1">Status</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ ucfirst($viewingSenderId->status) }}
                                    </span>
                                </div>

                                <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-100">
                                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-widest block mb-1">Purpose</span>
                                    <p class="text-zinc-600 text-sm leading-relaxed">{{ $viewingSenderId->purpose }}</p>
                                </div>

                                @if($viewingSenderId->status === 'rejected' && $viewingSenderId->reason)
                                    <div class="bg-rose-50 p-4 rounded-lg border border-rose-100">
                                        <span class="text-xs font-bold text-rose-400 uppercase tracking-widest block mb-1">Rejection Reason</span>
                                        <p class="text-rose-600 text-sm italic">{{ $viewingSenderId->reason }}</p>
                                    </div>
                                @endif
                                
                                <div class="flex items-center justify-between text-xs text-zinc-400 mt-2">
                                    <span>Created: {{ $viewingSenderId->created_at->format('M d, Y H:i') }}</span>
                                    <span>Last Updated: {{ $viewingSenderId->updated_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        @else
                           <div class="py-8 text-center text-zinc-400 italic">No details loaded.</div>
                        @endif

                        <div class="mt-8">
                            <button @click="$wire.showDetailsModal = false" class="w-full py-3 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-bold rounded-lg transition-colors">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>



    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            lucide.createIcons();

            Livewire.on('close-modal', () => {
                // Logic handled by entangle
            });

            Livewire.on('init-sender-id-payment', (event) => {
                const data = event[0] || event;
                const senderId = data.senderId;
                const gateway = data.gateway;

                // Fetch config from backend
                fetch(`{{ route('billing.checkout') }}?type=sender_id&id=${senderId}&gateway=${gateway}&mode=inline`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(async response => {
                        const contentType = response.headers.get("content-type");
                        const isJson = contentType && contentType.includes("application/json");

                        if (!response.ok) {
                            if (isJson) {
                                const errorData = await response.json();
                                throw new Error(errorData.error || 'Server returned an error.');
                            }
                            throw new Error('Server returned ' + response.status + '.');
                        }
                        return response.json();
                    })
                    .then(config => {
                        if (config.error) {
                            new ToastMagic().error("Payment Error", config.error);
                            return;
                        }

                        const handler = PaystackPop.setup({
                            key: config.key,
                            email: config.email,
                            amount: config.amount,
                            currency: config.currency,
                            ref: config.ref,
                            metadata: { custom_fields: [] },
                            callback: function (response) {
                                window.location.href = config.callback_url + '?reference=' + response.reference;
                            },
                            onClose: function () {
                                new ToastMagic().warning("Payment Cancelled", "Transaction was not completed.");
                            }
                        });
                        handler.openIframe();
                    })
                    .catch(error => {
                        console.error('Payment Error:', error);
                        new ToastMagic().error("Initialization Failed", error.message);
                    });
            });
        });

        document.addEventListener('livewire:navigated', () => lucide.createIcons());
    </script>
</div>