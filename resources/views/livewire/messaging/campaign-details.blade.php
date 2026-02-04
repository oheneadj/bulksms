<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('messaging.campaigns') }}"
                    class="text-[10px] font-bold text-zinc-400 hover:text-indigo-brand uppercase tracking-widest transition-colors">Campaigns</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-zinc-300"></i>
                <span class="text-[10px] font-bold text-indigo-brand uppercase tracking-widest">Detail</span>
            </div>
            <h2 class="text-3xl font-extrabold text-zinc-900 tracking-tight leading-tight">{{ $campaign->name }}</h2>
            <p class="text-zinc-500 text-sm mt-1 font-medium">Detailed performance and message logs for this campaign.
            </p>
        </div>
    </div>

    <!-- Stats Grid -->
    <!-- Stats & Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Delivery Status Distribution (Donut Chart) -->
        <div
            class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 flex flex-col items-center justify-center relative overflow-hidden">
            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-4 absolute top-6 left-6">Delivery
                Status</h3>
            <div id="campaignStatusChart" class="w-full h-48 flex items-center justify-center"></div>
        </div>

        <!-- Success Rate Ring -->
        <div
            class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 flex flex-col items-center justify-center relative">
            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-4 absolute top-6 left-6">Delivery
                Rate</h3>
            <div class="relative  w-40 h-40 flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="80" cy="80" r="70" stroke="currentColor" stroke-width="12" fill="transparent"
                        class="text-zinc-100" />
                    <circle cx="80" cy="80" r="70" stroke="currentColor" stroke-width="12" fill="transparent"
                        :stroke-dasharray="440" :stroke-dashoffset="440 - (440 * {{ $stats['delivery_rate'] }} / 100)"
                        class="text-emerald-500 transition-all duration-1000 ease-out" />
                </svg>
                <div class="absolute flex flex-col items-center">
                    <span class="text-3xl font-extrabold text-zinc-900">{{ $stats['delivery_rate'] }}%</span>
                </div>
            </div>
            <p class="text-[10px] font-medium text-zinc-400 mt-2">of {{ number_format($stats['total']) }} messages</p>
        </div>

        <!-- Cost & Summary -->
        <div class="grid grid-rows-2 gap-6">
            <div
                class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 ring-1 ring-indigo-500/10 flex flex-col justify-center">
                <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider mb-2">Total Budget</p>
                <p class="text-4xl font-extrabold text-zinc-900">${{ number_format($campaign->total_cost, 2) }}</p>
                <p class="text-xs text-zinc-400 mt-1">
                    ~${{ $stats['total'] > 0 ? number_format($campaign->total_cost / $stats['total'], 3) : 0 }} avg per
                    msg</p>
            </div>
            <div class="bg-zinc-50 border border-zinc-100 rounded-xl p-6 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-1">Failed</p>
                    <p class="text-xl font-extrabold text-rose-600">{{ number_format($stats['failed']) }}</p>
                </div>
                <div class="h-8 w-px bg-zinc-200"></div>
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-1">Pending</p>
                    <p class="text-xl font-extrabold text-amber-500">{{ number_format($stats['pending']) }}</p>
                </div>
                <div class="h-8 w-px bg-zinc-200"></div>
                <div>
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-1">Total</p>
                    <p class="text-xl font-extrabold text-zinc-900">{{ number_format($stats['total']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts for Charts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            loadCharts();
        });

        function loadCharts() {
            var options = {
                series: @json($chartData['series']),
                chart: {
                    type: 'donut',
                    height: 200,
                    fontFamily: 'Inter, sans-serif'
                },
                labels: @json($chartData['labels']),
                colors: @json($chartData['colors']),
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                tooltip: {
                    y: {
                        formatter: function (val) { return val + " msgs" }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#campaignStatusChart"), options);
            chart.render();
        }
    </script>

    <!-- Message Logs -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm overflow-hidden flex flex-col">
        <div
            class="px-6 py-4 border-b border-zinc-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
            <h2 class="text-base font-semibold text-zinc-900">Message Logs</h2>
            <div class="flex items-center gap-3">
                <select wire:model.live="statusFilter"
                    class="text-sm bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-2 outline-none">
                    <option value="">All Statuses</option>
                    <option value="delivered">Delivered</option>
                    <option value="failed">Failed</option>
                    <option value="queued">Queued</option>
                </select>
                <div class="relative max-w-xs">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Recipient..."
                        class="pl-9 pr-4 py-2 text-sm w-full bg-zinc-50 border border-zinc-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-100">
                <thead class="bg-zinc-50/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Recipient</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Message View</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            Sent At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($messages as $message)
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-xs">{{ $message->recipient }}</td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-zinc-600 line-clamp-1 max-w-sm">{{ $message->body }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $sc = [
                                        'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'queued' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    ];
                                    $color = $sc[$message->status] ?? 'bg-zinc-50 text-zinc-700 border-zinc-200';
                                @endphp
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 border rounded-full text-[10px] font-bold uppercase tracking-wider {{ $color }}">
                                    {{ $message->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs text-zinc-500">
                                {{ $message->created_at->format('M d, H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-zinc-100">
            {{ $messages->links() }}
        </div>
    </div>
</div>