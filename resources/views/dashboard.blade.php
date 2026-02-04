@php
    // Logic moved to App\Http\Controllers\DashboardController
@endphp

<x-layouts::app.sidebar>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="flex flex-col gap-8">
        <!-- Welcome Header -->
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-8 h-0.5 bg-indigo-brand rounded-full"></span>
                    <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-indigo-brand">Welcome
                        back</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-zinc-900 tracking-tight mb-2">{{ $user->name }}</h1>
                <p class="text-zinc-500 font-medium">Here's what's happening with your SMS campaigns today.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('messaging.send') }}"
                    class="bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-3 px-6 rounded-[6px] shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Send SMS
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Messages -->
            <div
                class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 hover:shadow-lg hover:shadow-indigo-500/5 transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i data-lucide="send" class="w-6 h-6 text-indigo-brand"></i>
                    </div>
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Total Sent</span>
                </div>
                <p class="text-3xl font-extrabold text-zinc-900 mb-1">{{ number_format($totalMessages) }}</p>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-zinc-500 font-medium">{{ number_format($messagesThisMonth) }} this
                        month</span>
                    @if($messageGrowth != 0)
                        <span
                            class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $messageGrowth > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $messageGrowth > 0 ? '+' : '' }}{{ round($messageGrowth) }}%
                        </span>
                    @endif
                </div>
            </div>

            <!-- Delivered -->
            <div
                class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 hover:shadow-lg hover:shadow-emerald-500/5 transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i data-lucide="check-circle" class="w-6 h-6 text-emerald-600"></i>
                    </div>
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Delivered</span>
                </div>
                <p class="text-3xl font-extrabold text-emerald-600 mb-1">{{ number_format($deliveredCount) }}</p>
                <p class="text-xs text-zinc-500 font-medium">{{ $deliveryRate }}% delivery rate</p>
            </div>

            <!-- Credits -->
            <div
                class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 hover:shadow-lg hover:shadow-amber-500/5 transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i data-lucide="coins" class="w-6 h-6 text-amber-600"></i>
                    </div>
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Credits</span>
                </div>
                <p class="text-3xl font-extrabold text-zinc-900 mb-1">{{ number_format($tenant->sms_credits ?? 0) }}</p>
                <p class="text-xs text-zinc-500 font-medium">Available balance</p>
            </div>

            <!-- Total Cost -->
            <div
                class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 hover:shadow-lg hover:shadow-blue-500/5 transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i data-lucide="dollar-sign" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">Total Spend</span>
                </div>
                <p class="text-3xl font-extrabold text-zinc-900 mb-1">${{ number_format($totalCost, 2) }}</p>
                <p class="text-xs text-zinc-500 font-medium">All time</p>
            </div>
        </div>

        <!-- Recent Campaigns (Quick Glance) -->
        @if($recentCampaigns->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($recentCampaigns as $campaign)
                    <a href="{{ route('messaging.campaign-details', $campaign->id) }}"
                        class="bg-white border border-zinc-200 rounded-xl p-5 hover:border-indigo-300 transition-all group">
                        <div class="flex items-center gap-3 mb-3">
                            <div
                                class="w-8 h-8 bg-zinc-100 rounded-lg flex items-center justify-center text-zinc-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-zinc-900 line-clamp-1">{{ $campaign->name }}</h4>
                                <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-wider">
                                    {{ $campaign->created_at->format('M d, g:i A') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-auto pt-3 border-t border-zinc-50">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-zinc-400 uppercase">Recipients</span>
                                <span
                                    class="text-xs font-extrabold text-zinc-900">{{ number_format($campaign->total_recipients) }}</span>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-[10px] font-bold text-zinc-400 uppercase">Status</span>
                                @php
                                    $stColors = [
                                        'sending' => 'text-indigo-600',
                                        'completed' => 'text-emerald-600',
                                        'failed' => 'text-rose-600',
                                        'scheduled' => 'text-amber-600',
                                    ];
                                    $ccColor = $stColors[$campaign->status] ?? 'text-zinc-600';
                                @endphp
                                <span class="text-xs font-extrabold capitalize {{ $ccColor }}">{{ $campaign->status }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-extrabold text-zinc-900 tracking-tight">Message Volume</h2>
                        <p class="text-sm text-zinc-500 font-medium mt-1">Daily trend for the last 7 days</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-indigo-brand rounded-sm"></span>
                        <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Sent Messages</span>
                    </div>
                </div>
                <div class="h-[250px]">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>

            <!-- Delivery Stats (Donut) -->
            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
                <div class="mb-4">
                    <h2 class="text-xl font-extrabold text-zinc-900 tracking-tight">Global Status</h2>
                    <p class="text-sm text-zinc-500 font-medium mt-1">Overall Delivery Performance</p>
                </div>
                <div class="h-[250px] relative">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-bold text-zinc-600 uppercase">Delivered</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <span class="text-xs font-bold text-zinc-600 uppercase">Failed</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span class="text-xs font-bold text-zinc-600 uppercase">Queued</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span class="text-xs font-bold text-zinc-600 uppercase">Total</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Messages (2/3 width) -->
            <livewire:dashboard.recent-messages />

            <!-- Quick Actions & Stats -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
                    <h3 class="text-sm font-bold text-zinc-900 mb-4 uppercase tracking-wider">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('messaging.send') }}"
                            class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 hover:bg-indigo-50 hover:border-indigo-200 transition-all group">
                            <div
                                class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-600 transition-colors">
                                <i data-lucide="send" class="w-5 h-5 text-indigo-600 group-hover:text-white"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-zinc-900">Send SMS</p>
                                <p class="text-xs text-zinc-500">Compose new message</p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400"></i>
                        </a>

                        <div class="border-t border-zinc-100 my-4 pt-4">
                            <h4 class="text-[10px] font-extrabold text-zinc-400 uppercase tracking-widest mb-3">Top
                                Groups</h4>
                            @forelse($topGroups as $group)
                                <div
                                    class="flex items-center justify-between py-2 group/item hover:bg-zinc-50 p-2 rounded-lg transition-colors">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-6 h-6 rounded bg-indigo-100 text-indigo-600 flex items-center justify-center text-[10px] font-bold">
                                            {{ substr($group->name, 0, 1) }}
                                        </div>
                                        <span class="text-xs font-bold text-zinc-700">{{ $group->name }}</span>
                                    </div>
                                    <span
                                        class="text-[10px] font-bold text-zinc-400">{{ number_format($group->contacts_count) }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-zinc-400 font-medium">No groups yet.</p>
                            @endforelse
                        </div>

                        <a href="{{ route('contacts') }}"
                            class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 hover:bg-emerald-50 hover:border-emerald-200 transition-all group mt-2">
                            <div
                                class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:bg-emerald-600 transition-colors">
                                <i data-lucide="users" class="w-5 h-5 text-emerald-600 group-hover:text-white"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-zinc-900">Manage Contacts</p>
                                <p class="text-xs text-zinc-500">{{ number_format($totalContacts) }} total</p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400"></i>
                        </a>

                        <a href="{{ route('billing') }}"
                            class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 hover:bg-amber-50 hover:border-amber-200 transition-all group">
                            <div
                                class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-600 transition-colors">
                                <i data-lucide="credit-card" class="w-5 h-5 text-amber-600 group-hover:text-white"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-zinc-900">Top Up</p>
                                <p class="text-xs text-zinc-500">Add more credits</p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400"></i>
                        </a>
                    </div>
                </div>

                <!-- Account Overview -->
                <div class="bg-indigo-500 rounded-xl p-6 text-white shadow-xl shadow-indigo-500/20">
                    <h3 class="text-sm font-bold mb-4 uppercase tracking-wider opacity-90">Account Status</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium opacity-90">Sender IDs</span>
                            <span class="text-lg font-extrabold">{{ $approvedSenderIds }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium opacity-90">Contacts</span>
                            <span class="text-lg font-extrabold">{{ number_format($totalContacts) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium opacity-90">Plan</span>
                            <span class="text-lg font-extrabold capitalize">{{ $tenant->plan_type ?? 'Free' }}</span>
                        </div>
                    </div>
                    <div class="mt-6 pt-6 border-t border-white/20">
                        <a href="{{ route('profile.edit') }}"
                            class="text-sm font-bold flex items-center gap-2 hover:underline">
                            Manage account
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            lucide.createIcons();
            const ctx = document.getElementById('trafficChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Messages Sent',
                        data: @json($chartData),
                        borderColor: '#612fff',
                        backgroundColor: 'rgba(97, 47, 255, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#612fff',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#18181b',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [5, 5], color: '#f4f4f5' },
                            ticks: { color: '#a1a1aa', font: { size: 11, weight: '500' }, stepSize: 1 }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#a1a1aa', font: { size: 11, weight: '500' } }
                        }
                    }
                }
            });

            // Status Donut Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Delivered', 'Failed', 'Queued', 'Scheduled'],
                    datasets: [{
                        data: [
                            {{ $statusCounts['delivered'] ?? 0 }},
                            {{ $statusCounts['failed'] ?? 0 }},
                            {{ $statusCounts['queued'] ?? 0 }},
                            {{ $statusCounts['scheduled'] ?? 0 }}
                        ],
                        backgroundColor: ['#10b981', '#f43f5e', '#f59e0b', '#8b5cf6'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });

        document.addEventListener('livewire:navigated', () => {
            lucide.createIcons();
        });
    </script>
</x-layouts::app.sidebar>