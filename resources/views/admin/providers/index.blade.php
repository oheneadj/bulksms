<x-layouts::admin title="SMS Gateways">
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-900">SMS Gateways</h1>
                <p class="text-sm text-zinc-500 mt-1">Manage your SMS service providers and routing priorities.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Left Column: Form -->
            <div class="xl:col-span-1">
                <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden sticky top-6">
                    <div class="px-6 py-4 border-b border-zinc-100 bg-zinc-50/50">
                        <h3 class="font-bold text-zinc-900 flex items-center gap-2">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-indigo-600"></i>
                            Connect New Gateway
                        </h3>
                    </div>
                    
                    <div class="p-6">
                        @if ($errors->any())
                            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 shrink-0 mt-0.5"></i>
                                    <div>
                                        <h3 class="text-sm font-bold text-red-800">Please correct the following errors:</h3>
                                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Main Form Container with Alpine Data Scope -->
                        <form action="{{ route('admin.providers.store') }}" method="POST" class="space-y-6"
                              x-data="{ currentProvider: '{{ old('provider', 'twilio') }}' }">
                            @csrf
                            
                            <!-- Basic Info -->
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Display Name</label>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-500">
                                            <i data-lucide="tag" class="w-4 h-4 text-zinc-400"></i>
                                        </div>
                                        <input type="text" name="name" value="{{ old('name') }}"
                                            class="pl-10 w-full rounded-lg border-zinc-200 bg-zinc-50/50 px-3 py-2.5 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm transition-all outline-none placeholder:text-zinc-400 @error('name') border-red-300 bg-red-50/50 focus:border-red-500 focus:ring-red-200 @enderror"
                                            placeholder="e.g. Primary Twilio Account">
                                    </div>
                                    @error('name') <span class="text-xs text-red-600 mt-1.5 block font-medium">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Provider</label>
                                        <div class="relative">
                                            <select name="provider" x-model="currentProvider"
                                                class="w-full rounded-lg border-zinc-200 bg-zinc-50/50 px-3 py-2.5 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm transition-all outline-none cursor-pointer appearance-none">
                                                <option value="twilio">Twilio</option>
                                                <option value="mnotify">mNotify</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">
                                            Priority
                                        </label>
                                        <input type="number" name="priority" value="{{ old('priority', 10) }}"
                                            class="w-full rounded-lg border-zinc-200 bg-zinc-50/50 px-3 py-2.5 text-sm focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm transition-all outline-none text-center">
                                    </div>
                                </div>
                            </div>

                            <hr class="border-zinc-100">

                            <!-- Configuration Section -->
                            <div class="min-h-[220px]"> <!-- Min height to prevent jumping -->
                                
                                <!-- Twilio Config -->
                                <div x-show="currentProvider === 'twilio'" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="space-y-4">
                                    
                                    <div class="flex items-center gap-2 mb-4 bg-red-50/50 p-3 rounded-lg border border-red-100">
                                        <div class="w-2 h-2 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.5)]"></div>
                                        <span class="text-xs font-bold text-red-900 uppercase tracking-wide">Twilio Configuration</span>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-600 mb-2">Account SID</label>
                                        <input type="text" name="sid" value="{{ old('sid') }}" placeholder="ACxxxxxxxxxxxxxxxx..."
                                            class="font-mono text-sm w-full rounded-lg border-zinc-200 bg-zinc-50/50 px-3 py-2.5 focus:bg-white focus:border-red-500 focus:ring-2 focus:ring-red-500/20 shadow-sm transition-all outline-none @error('sid') border-red-300 bg-red-50/50 @enderror">
                                        @error('sid') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-600 mb-2">Auth Token</label>
                                        <input type="password" name="token" placeholder="••••••••••••••••••••••••"
                                            class="font-mono text-sm w-full rounded-lg border-zinc-200 bg-zinc-50/50 px-3 py-2.5 focus:bg-white focus:border-red-500 focus:ring-2 focus:ring-red-500/20 shadow-sm transition-all outline-none @error('token') border-red-300 bg-red-50/50 @enderror">
                                        @error('token') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-600 mb-2">From Number</label>
                                        <input type="text" name="from" value="{{ old('from') }}" placeholder="+1234567890"
                                            class="w-full rounded-lg border-zinc-200 bg-zinc-50/50 px-3 py-2.5 text-sm focus:bg-white focus:border-red-500 focus:ring-2 focus:ring-red-500/20 shadow-sm transition-all outline-none @error('from') border-red-300 bg-red-50/50 @enderror">
                                        @error('from') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- mNotify Config -->
                                <div x-show="currentProvider === 'mnotify'" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     style="display: none;"
                                     class="space-y-4">
                                    
                                    <div class="flex items-center gap-2 mb-4 bg-emerald-50/50 p-3 rounded-lg border border-emerald-100">
                                        <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                                        <span class="text-xs font-bold text-emerald-900 uppercase tracking-wide">mNotify Configuration</span>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-zinc-600 mb-2">API Key</label>
                                        <input type="password" name="key" value="{{ old('key') }}" placeholder="Enter your mNotify API key"
                                            class="font-mono text-sm w-full rounded-lg border-zinc-200 bg-zinc-50/50 px-3 py-2.5 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-sm transition-all outline-none @error('key') border-red-300 bg-red-50/50 @enderror">
                                        @error('key') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-600 mb-2">Default Sender ID</label>
                                        <input type="text" name="sender_id" value="{{ old('sender_id') }}" placeholder="e.g. MyBrand"
                                            class="w-full rounded-lg border-zinc-200 bg-zinc-50/50 px-3 py-2.5 text-sm focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 shadow-sm transition-all outline-none @error('sender_id') border-red-300 bg-red-50/50 @enderror">
                                        <p class="text-[10px] text-zinc-400 mt-1.5 flex items-center gap-1">
                                            <i data-lucide="info" class="w-3 h-3"></i>
                                            Must be locally approved on mNotify first
                                        </p>
                                        @error('sender_id') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full bg-zinc-900 text-white font-bold py-3 px-4 rounded-xl hover:bg-zinc-800 hover:shadow-lg hover:shadow-zinc-900/10 active:scale-[0.98] transition-all flex items-center justify-center gap-2 group mt-4">
                                <span>Save Gateway</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: List -->
            <div class="xl:col-span-2 space-y-6">
                
                <!-- Info Banner -->
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex items-start gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-indigo-600 shrink-0 mt-0.5"></i>
                    <div class="text-sm text-indigo-900">
                        <p class="font-bold mb-1">Routing Logic</p>
                        <p class="opacity-90">The system automatically selects the highest priority active gateway for each message. If a gateway fails, it will attempt the next one in priority order.</p>
                    </div>
                </div>

                <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-100 bg-zinc-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <h3 class="font-bold text-zinc-900 flex items-center gap-2">
                            <i data-lucide="list-checks" class="w-4 h-4 text-zinc-500"></i>
                            Active Gateways
                        </h3>
                        <div class="flex items-center gap-2 text-xs text-zinc-500 font-medium bg-white border border-zinc-200 px-3 py-1.5 rounded-lg shadow-sm">
                            <div class="w-2 h-2 rounded-full bg-green-500"></div> Connected
                            <div class="w-px h-3 bg-zinc-200 mx-1"></div>
                            <div class="w-2 h-2 rounded-full bg-zinc-300"></div> Disabled
                        </div>
                    </div>

                    @if($providers->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-zinc-50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider border-b border-zinc-100">
                                    <tr>
                                        <th class="px-6 py-3 w-16 text-center">Pri.</th>
                                        <th class="px-6 py-3">Gateway Name</th>
                                        <th class="px-6 py-3">Type</th>
                                        <th class="px-6 py-3">Config</th>
                                        <th class="px-6 py-3">Status</th>
                                        <th class="px-6 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-50">
                                    @foreach($providers as $provider)
                                        <tr class="group hover:bg-zinc-50/50 transition-colors">
                                            <td class="px-6 py-4 text-center">
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-zinc-100 text-zinc-600 font-mono font-bold text-xs">
                                                    {{ $provider->priority }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-zinc-900">{{ $provider->name }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($provider->provider === 'twilio')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Twilio
                                                    </span>
                                                @elseif($provider->provider === 'mnotify')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> mNotify
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-zinc-100 text-zinc-700">
                                                        {{ ucfirst($provider->provider) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-xs font-mono text-zinc-500">
                                                @if($provider->provider === 'twilio')
                                                    SID: {{ Str::limit($provider->config['sid'] ?? 'N/A', 8) }}
                                                @elseif($provider->provider === 'mnotify')
                                                    Sender: {{ $provider->config['sender_id'] ?? 'Default' }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <form action="{{ route('admin.providers.toggle', $provider->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" 
                                                        class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 {{ $provider->is_active ? 'bg-indigo-600' : 'bg-zinc-200' }}"
                                                        role="switch" 
                                                        aria-checked="{{ $provider->is_active ? 'true' : 'false' }}">
                                                        <span aria-hidden="true" 
                                                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $provider->is_active ? 'translate-x-4' : 'translate-x-0' }}">
                                                        </span>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <form action="{{ route('admin.providers.destroy', $provider->id) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete {{ $provider->name }}? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                        class="text-zinc-400 hover:text-red-600 p-1.5 hover:bg-red-50 rounded-lg transition-all group-hover:opacity-100 opacity-60"
                                                        title="Delete Gateway">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <div class="w-16 h-16 bg-zinc-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="server-off" class="w-8 h-8 text-zinc-300"></i>
                            </div>
                            <h3 class="text-zinc-900 font-bold mb-1">No Gateways Connected</h3>
                            <p class="text-zinc-500 text-sm max-w-xs mx-auto">Add your first SMS provider using the form on the left to start sending messages.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts::admin>