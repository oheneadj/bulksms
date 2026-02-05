<x-layouts::admin title="Admin Dashboard">
    <div class="flex flex-col gap-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Tenants -->
            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center">
                        <i data-lucide="building-2" class="w-6 h-6 text-indigo-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-zinc-900 mb-1">{{ number_format($totalTenants) }}</p>
                <p class="text-xs text-zinc-500 font-medium">Total Tenants</p>
            </div>

            <!-- Total Messages -->
            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                        <i data-lucide="message-square" class="w-6 h-6 text-emerald-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-zinc-900 mb-1">{{ number_format($totalMessages) }}</p>
                <p class="text-xs text-zinc-500 font-medium">Total Messages Sent</p>
            </div>

            <!-- Total Credits Volume -->
            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                        <i data-lucide="coins" class="w-6 h-6 text-amber-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-zinc-900 mb-1">{{ number_format($totalTransactions) }}</p>
                <p class="text-xs text-zinc-500 font-medium">Credits Purchased Volume</p>
            </div>
        </div>

        <!-- Provider Balances -->
        @if(isset($providerBalances) && count($providerBalances) > 0)
            <div class="mb-8">
                <h3 class="font-bold text-zinc-900 mb-4 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <i data-lucide="wallet" class="w-5 h-5 text-indigo-600"></i>
                        Provider Balances
                    </div>
                    <form action="{{ route('admin.providers.sync-balances') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-zinc-800 hover:bg-zinc-700 rounded-lg transition-colors">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            Sync Credits
                        </button>
                    </form>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($providerBalances as $provider)
                        <div
                            class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6 relative overflow-hidden group hover:border-indigo-300 transition-all">
                            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                <i data-lucide="server" class="w-24 h-24 text-indigo-900"></i>
                            </div>
                            <div class="flex items-center justify-between mb-4 relative z-10">
                                <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center">
                                    <i data-lucide="cloud-lightning" class="w-5 h-5 text-indigo-600"></i>
                                </div>
                                <span
                                    class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full uppercase tracking-wider border border-emerald-100">Active</span>
                            </div>
                            <h4 class="text-sm font-bold text-zinc-500 uppercase tracking-wider">{{ $provider['name'] }}</h4>
                            <div class="mt-1">
                                <p class="text-3xl font-extrabold text-zinc-900 tracking-tight">
                                    {{ number_format($provider['balance']) }}
                                    <span
                                        class="text-xs font-bold text-zinc-400 align-top mt-1 inline-block">{{ $provider['currency'] }}</span>
                                </p>
                                @if(isset($provider['bonus']) && $provider['bonus'] > 0)
                                    <div
                                        class="flex items-center gap-1.5 mt-2 text-emerald-600 font-bold text-xs bg-emerald-50 inline-flex px-2 py-1 rounded-full">
                                        <i data-lucide="gift" class="w-3 h-3"></i>
                                        +{{ number_format($provider['bonus']) }} Bonus
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Secondary Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- Low Balance Tenants -->
            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden flex flex-col h-full">
                <div class="px-6 py-4 border-b border-zinc-100 flex justify-between items-center bg-red-50/50">
                    <h3 class="font-bold text-red-900 flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                        Low Balance Tenants
                    </h3>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Tenant</th>
                                <th class="px-6 py-3 text-right">Credits</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($lowBalanceTenants as $tenant)
                                <tr class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="px-6 py-3 font-medium text-zinc-900">{{ $tenant->name }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                            {{ number_format($tenant->sms_credits) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <a href="{{ route('admin.tenants.impersonate', $tenant->id) }}"
                                            class="text-xs font-bold text-indigo-600 hover:text-indigo-700">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-zinc-400 text-xs">
                                        All tenants have healthy balances.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Tenants -->
            <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden flex flex-col h-full">
                <div class="px-6 py-4 border-b border-zinc-100 flex justify-between items-center">
                    <h3 class="font-bold text-zinc-900">Recent Tenants</h3>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3 text-right">Joined</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach ($recentTenants as $tenant)
                                <tr class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="px-6 py-3">
                                        <div class="font-bold text-zinc-900">{{ $tenant->name }}</div>
                                        <div class="text-[10px] text-zinc-500">{{ number_format($tenant->sms_credits) }}
                                            credits</div>
                                    </td>
                                    <td class="px-6 py-3 text-right text-zinc-500 text-xs">
                                        {{ $tenant->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <a href="{{ route('admin.tenants.impersonate', $tenant->id) }}"
                                            class="text-xs font-bold text-indigo-600 hover:text-indigo-700">Login as</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 30-Day Activity Chart Placeholder (Requires Chart.js or similar, putting basic textual summary for now if needed, or we can leave it for the next iteration) -->
        <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
            <h3 class="font-bold text-zinc-900 mb-4">30-Day Activity</h3>
            <!-- Simple CSS Bar Chart for Messages -->
            <div class="flex items-end gap-1 h-32 w-full">
                @foreach($chartData['messages'] as $count)
                        @php 
                                                                    $max = max($chartData['messages']) ?: 1;
                            $height = ($count / $max) * 100;
                        @endphp
                    <div class="flex-1 bg-indigo-100 hover:bg-indigo-200 rounded-t-sm relative group" style="height: {{ max($height, 5) }}%;">
                        <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 bg-zinc-800 text-white text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity z-10 whitespace-nowrap">
                                        {{ $count }} msgs
                                    </div>
                                </div>
                @endforeach
            </div>
            <div class="flex justify-between text-[10px] text-zinc-400 mt-2">
                <span>30 Days ago</span>
                <span>Today</span>
            </div>
        </div>
    </div>
</x-layouts::admin>