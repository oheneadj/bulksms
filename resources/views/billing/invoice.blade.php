<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $transaction->reference }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body class="bg-gray-100 p-8 min-h-screen">

    <div
        class="max-w-3xl mx-auto bg-white p-12 rounded-xl shadow-sm border border-gray-200 print:shadow-none print:border-none">

        <!-- Header -->
        <div class="flex justify-between items-start mb-12">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">INVOICE</h1>
                <p class="text-gray-500 font-medium text-sm">#{{ $transaction->reference }}</p>
                @if($transaction->type === 'deposit')
                    <span
                        class="inline-block mt-4 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-wide">PAID</span>
                @else
                    <span
                        class="inline-block mt-4 px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-bold uppercase tracking-wide">USAGE</span>
                @endif
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-indigo-600 mb-1">{{ config('app.name') }}</div>
                <div class="text-sm text-gray-500">
                    123 Tech Street<br>
                    Accra, Ghana<br>
                    support@bulksms.com
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-2 gap-12 mb-12">
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Bill To</h3>
                <div class="text-gray-900 font-bold">{{ $transaction->user->name }}</div>
                <div class="text-gray-600 text-sm">{{ $transaction->user->email }}</div>
                <div class="text-gray-600 text-sm mt-1">{{ $transaction->user->tenant->name }}</div>
            </div>
            <div class="text-right">
                <div class="mb-4">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Date</h3>
                    <div class="text-gray-900 font-medium">{{ $transaction->created_at->format('M d, Y') }}</div>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Payment Method</h3>
                    <div class="text-gray-900 font-medium capitalize">
                        {{ Str::of($transaction->description)->after('via ')->before(' ')->toString() ?: 'System' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="mb-12">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="py-3 text-xs font-bold text-gray-400 uppercase tracking-widest w-1/2">Description
                        </th>
                        <th class="py-3 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Credits
                        </th>
                        <th class="py-3 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Amount
                        </th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <tr class="border-b border-gray-100">
                        <td class="py-5 text-gray-900 font-medium">
                            <p class="font-bold mb-1">
                                {{ Str::of($transaction->description)->before(' (')->toString() ?: 'Credits Purchase' }}
                            </p>
                            <p class="text-gray-500 text-xs">Wallet top-up transaction</p>
                        </td>
                        <td class="py-5 text-right font-mono text-gray-700">{{ number_format($transaction->amount) }}
                        </td>
                        <td class="py-5 text-right font-bold text-gray-900">
                            {{-- Parse amount from description if possible, else 0 --}}
                            @php
                                preg_match('/\(([\d\.]+)\)/', $transaction->description, $matches);
                                $fiatAmount = $matches[1] ?? 0;
                             @endphp
                            {{ $fiatAmount > 0 ? number_format($fiatAmount, 2) : '-' }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="pt-6"></td>
                        <td class="pt-6 text-right text-sm font-bold text-gray-500">Total</td>
                        <td class="pt-6 text-right text-2xl font-extrabold text-gray-900">
                            {{ $fiatAmount > 0 ? number_format($fiatAmount, 2) : '-' }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 pt-8 text-center">
            <p class="text-gray-500 text-sm mb-4">Thank you for your business!</p>
            <button onclick="window.print()"
                class="no-print inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-printer">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect width="12" height="8" x="6" y="14" />
                </svg>
                Print Invoice
            </button>
        </div>

    </div>

</body>

</html>