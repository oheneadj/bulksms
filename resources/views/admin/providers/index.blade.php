<x-layouts::admin title="SMS Providers">
    <div class="space-y-6">
        <!-- Add New Provider Form -->
        <div class="bg-white border border-zinc-200 shadow-sm rounded-xl p-6">
            <h3 class="font-bold text-zinc-900 mb-4">Add New Provider</h3>
            <form action="{{ route('admin.providers.store') }}" method="POST"
                class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-zinc-500 uppercase mb-1">Name</label>
                    <input type="text" name="name"
                        class="w-full rounded-lg border-zinc-200 text-sm focus:ring-indigo-500"
                        placeholder="e.g. Primary Twilio">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zinc-500 uppercase mb-1">Provider Type</label>
                    <select name="provider" class="w-full rounded-lg border-zinc-200 text-sm focus:ring-indigo-500">
                        <option value="twilio">Twilio</option>
                        <option value="mnotify">mNotify</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zinc-500 uppercase mb-1">Priority</label>
                    <input type="number" name="priority" value="10"
                        class="w-full rounded-lg border-zinc-200 text-sm focus:ring-indigo-500">
                </div>

                <!-- Twilio Fields -->
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-zinc-100 pt-4">
                    <p class="md:col-span-3 text-xs font-bold text-indigo-600">Twilio Config</p>
                    <input type="text" name="sid" placeholder="Account SID"
                        class="w-full rounded-lg border-zinc-200 text-sm">
                    <input type="password" name="token" placeholder="Auth Token"
                        class="w-full rounded-lg border-zinc-200 text-sm">
                    <input type="text" name="from" placeholder="From Number"
                        class="w-full rounded-lg border-zinc-200 text-sm">
                </div>

                <!-- mNotify Fields -->
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-zinc-100 pt-4">
                    <p class="md:col-span-2 text-xs font-bold text-emerald-600">mNotify Config</p>
                    <input type="password" name="key" placeholder="API Key"
                        class="w-full rounded-lg border-zinc-200 text-sm">
                    <input type="text" name="sender_id" placeholder="Default Sender ID"
                        class="w-full rounded-lg border-zinc-200 text-sm">
                </div>

                <div class="md:col-span-2">
                    <button type="submit"
                        class="bg-indigo-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-indigo-700 transition-colors">
                        Add Provider
                    </button>
                </div>
            </form>
        </div>

        <!-- Providers List -->
        <div class="bg-white border border-zinc-200 shadow-sm rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-100">
                <h3 class="font-bold text-zinc-900">Active Providers</h3>
            </div>
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-zinc-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Priority</th>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach($providers as $provider)
                        <tr class="hover:bg-zinc-50/50">
                            <td class="px-6 py-4 font-mono font-bold">{{ $provider->priority }}</td>
                            <td class="px-6 py-4 font-bold text-zinc-900">{{ $provider->name }}</td>
                            <td class="px-6 py-4 uppercase text-xs font-bold text-zinc-500">{{ $provider->provider }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 rounded text-xs font-bold {{ $provider->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $provider->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.providers.toggle', $provider->id) }}" method="POST"
                                    class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-indigo-600 hover:underline mr-3">
                                        {{ $provider->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.providers.destroy', $provider->id) }}" method="POST"
                                    class="inline" onsubmit="return confirm('Delete this provider?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-xs font-bold text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts::admin>