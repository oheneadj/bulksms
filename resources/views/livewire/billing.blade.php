<div class="px-6 md:px-10 py-10 max-w-[1440px] mx-auto text-zinc-900">
    <div class="flex flex-col md:flex-row justify-between md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight mb-2 text-zinc-900">{{ __('Billing & Credits') }}</h1>
            <p class="text-zinc-500 font-medium">{{ __('Manage your account balance and view transaction history.') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span
                class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 border border-emerald-100 rounded-full text-emerald-700 text-xs font-bold uppercase tracking-wider">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Active Plan: Free
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Balance Card -->
        <div class="lg:col-span-1 bg-white border border-zinc-200 shadow-sm rounded-xl p-8 sticky top-10 self-start">
            <h2 class="text-sm font-semibold text-zinc-900 mb-6 uppercase tracking-wider">{{ __('Available Balance') }}
            </h2>

            <div class="flex items-baseline gap-1 mb-10">
                <span
                    class="text-5xl font-extrabold text-indigo-brand tracking-tighter">{{ number_format(auth()->user()->tenant->sms_credits ?? 0) }}</span>
                <span class="text-lg font-bold text-zinc-400">{{ __('Credits') }}</span>
            </div>

            <form wire:submit="topUp" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-zinc-900 mb-3">{{ __('Select Package') }}</label>
                    <div class="space-y-3">
                        @forelse($packages as $package)
                            <label
                                class="relative flex items-center justify-between p-4 border rounded-xl cursor-pointer transition-all hover:bg-zinc-50 {{ $selectedPackageId == $package->id ? 'border-indigo-600 bg-indigo-50/30 ring-1 ring-indigo-600' : 'border-zinc-200' }}">
                                <input type="radio" wire:model.live="selectedPackageId" value="{{ $package->id }}"
                                    class="sr-only">
                                <div>
                                    <span class="block text-sm font-bold text-zinc-900">{{ $package->name }}</span>
                                    <span
                                        class="block text-xs text-zinc-500 font-medium mt-0.5">{{ number_format($package->credits) }}
                                        Credits</span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-lg font-extrabold text-indigo-brand">{{ $package->currency }}
                                        {{ number_format($package->price, 2) }}</span>
                                </div>
                            </label>
                        @empty
                            <div
                                class="p-4 border border-zinc-200 border-dashed rounded-xl text-center text-sm text-zinc-500">
                                No packages available at the moment.
                            </div>
                        @endforelse
                    </div>
                    @error('selectedPackageId') <span
                    class="text-rose-600 text-xs font-bold mt-2 block">{{ $message }}</span> @enderror
                </div>

                <div class="pt-2">
                    <label class="block text-sm font-semibold text-zinc-900 mb-2">{{ __('Payment Method') }}</label>
                    <div class="space-y-2">
                        <!-- Stripe -->
                        <label
                            class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-gray-50 {{ $gateway === 'stripe' ? 'border-indigo-500 bg-indigo-50/50' : 'border-zinc-200' }}">
                            <input type="radio" wire:model.live="gateway" value="stripe" name="gateway" class="hidden">
                            <div
                                class="w-4 h-4 rounded-full border border-gray-300 flex items-center justify-center {{ $gateway === 'stripe' ? 'border-indigo-600' : '' }}">
                                @if($gateway === 'stripe')
                                <div class="w-2 h-2 bg-indigo-600 rounded-full"></div> @endif
                            </div>
                            <div class="flex-1 flex items-center justify-between">
                                <span class="text-sm font-bold text-zinc-700">Card Payment (Stripe)</span>
                                <i data-lucide="credit-card" class="w-4 h-4 text-gray-400"></i>
                            </div>
                        </label>

                        <!-- Paystack -->
                        <label
                            class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-gray-50 {{ $gateway === 'paystack' ? 'border-indigo-500 bg-indigo-50/50' : 'border-zinc-200' }}">
                            <input type="radio" wire:model.live="gateway" value="paystack" name="gateway"
                                class="hidden">
                            <div
                                class="w-4 h-4 rounded-full border border-gray-300 flex items-center justify-center {{ $gateway === 'paystack' ? 'border-indigo-600' : '' }}">
                                @if($gateway === 'paystack')
                                <div class="w-2 h-2 bg-indigo-600 rounded-full"></div> @endif
                            </div>
                            <div class="flex-1 flex items-center justify-between">
                                <span class="text-sm font-bold text-zinc-700">Paystack (GHS)</span>
                                <span
                                    class="text-[10px] font-bold bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Mobile
                                    Money</span>
                            </div>
                        </label>

                        <!-- Flutterwave -->
                        <label
                            class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all hover:bg-gray-50 {{ $gateway === 'flutterwave' ? 'border-indigo-500 bg-indigo-50/50' : 'border-zinc-200' }}">
                            <input type="radio" wire:model.live="gateway" value="flutterwave" name="gateway"
                                class="hidden">
                            <div
                                class="w-4 h-4 rounded-full border border-gray-300 flex items-center justify-center {{ $gateway === 'flutterwave' ? 'border-indigo-600' : '' }}">
                                @if($gateway === 'flutterwave')
                                <div class="w-2 h-2 bg-indigo-600 rounded-full"></div> @endif
                            </div>
                            <div class="flex-1 flex items-center justify-between">
                                <span class="text-sm font-bold text-zinc-700">Flutterwave (GHS)</span>
                                <span
                                    class="text-[10px] font-bold bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">MoMo/Card</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-[#612fff] hover:bg-indigo-brand-hover text-white font-bold py-3.5 px-6 rounded-[6px] shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        <span>{{ __('Add Credits') }}</span>
                    </button>
                    <p class="text-[10px] text-zinc-400 text-center mt-3 leading-relaxed">
                        This is a mock payment for demonstration purposes. <br> No real card is charged.
                    </p>
                </div>
            </form>
        </div>

        <!-- Transactions History -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden">
                <div
                    class="px-8 py-6 border-b border-zinc-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h3 class="font-bold text-zinc-900">{{ __('Transaction History') }}</h3>
                    <div class="relative w-full sm:w-64">
                        <i data-lucide="search"
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search transactions..."
                            class="w-full pl-9 pr-4 py-2 text-xs border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-zinc-900 placeholder:text-zinc-400">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50/50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-8 py-4">{{ __('Reference') }}</th>
                                <th class="px-8 py-4">{{ __('Description') }}</th>
                                <th class="px-8 py-4 text-right">{{ __('Amount') }}</th>
                                <th class="px-8 py-4 text-right hidden md:table-cell">{{ __('Date') }}</th>
                                <th class="px-8 py-4 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($transactions as $transaction)
                                <tr class="hover:bg-zinc-50/50 transition-colors group">
                                    <td
                                        class="px-8 py-4 font-mono text-xs font-medium text-zinc-500 group-hover:text-indigo-600 transition-colors">
                                        {{ $transaction->reference }}
                                    </td>
                                    <td class="px-8 py-4 font-medium text-zinc-900">
                                        {{ $transaction->description }}
                                        <div class="md:hidden text-[10px] text-zinc-400 mt-1">
                                            {{ $transaction->created_at->format('M d, Y h:i A') }}
                                        </div>
                                    </td>
                                    <td class="px-8 py-4 text-right">
                                        <span
                                            class="inline-flex items-center gap-1 font-bold {{ $transaction->type === 'deposit' ? 'text-emerald-600' : 'text-zinc-900' }}">
                                            {{ $transaction->type === 'deposit' ? '+' : '-' }}{{ number_format($transaction->amount) }}
                                        </span>
                                        @if($transaction->type === 'deposit')
                                        @endif
                                    </td>
                                    <td class="px-8 py-4 text-right text-zinc-500 font-medium hidden md:table-cell">
                                        {{ $transaction->created_at->format('M d, Y') }}
                                        <span class="text-zinc-300 mx-1">|</span>
                                        {{ $transaction->created_at->format('h:i A') }}
                                    </td>
                                    <td class="px-8 py-4 text-right">
                                        @if($transaction->type === 'deposit')
                                            <a href="{{ route('billing.invoice', $transaction->id) }}" target="_blank"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-white bg-indigo-400 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg text-xs font-bold transition-all border border-zinc-200">
                                                <i data-lucide="download" class="w-3 h-3"></i>
                                                Invoice
                                            </a>
                                        @else
                                            <span class="text-zinc-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div
                                                class="w-12 h-12 bg-zinc-50 rounded-full flex items-center justify-center mb-4">
                                                <i data-lucide="receipt" class="w-6 h-6 text-zinc-300"></i>
                                            </div>
                                            <p class="text-zinc-500 font-medium">{{ __('No transactions found') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($transactions->hasPages())
                    <div class="px-8 py-4 border-t border-zinc-100">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('init-paystack-payment', (event) => {
            const data = event[0] || event; // Handle potentially wrapped event data
            const packageId = data.packageId;
            const gateway = data.gateway;

            // Fetch config from backend
            fetch(`{{ route('billing.checkout') }}?package_id=${packageId}&gateway=${gateway}&mode=inline`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(async response => {
                    const contentType = response.headers.get("content-type");
                    const isJson = contentType && contentType.includes("application/json");

                    if (!response.ok) {
                        // Try to read JSON error
                        if (isJson) {
                            const errorData = await response.json();
                            throw new Error(errorData.error || 'Server returned an error.');
                        }
                        // If not JSON, probably HTML error page
                        throw new Error('Server returned ' + response.status + ' ' + response.statusText + '. Please check server logs.');
                    }

                    if (!isJson) {
                        // If 200 OK but HTML, it means a Redirect was followed to the billing page or login page
                        throw new Error("Received non-JSON response (HTML). Possible redirect or authentication issue.");
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
                        ref: config.ref, // Generated by backend
                        // access_code: config.access_code, // Prefer access_code if available
                        metadata: {
                            custom_fields: []
                        },
                        callback: function (response) {
                            // Redirect to backend callback for verification
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
</script>