<x-layouts::app.sidebar>
    <div class="max-w-5xl mx-auto py-10 px-4">
        <div class="mb-12">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-8 h-0.5 bg-indigo-600 rounded-full"></span>
                <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-indigo-600">Developer
                    Portal</span>
            </div>
            <h1 class="text-4xl font-extrabold text-zinc-900 tracking-tight mb-4">REST API Documentation</h1>
            <p class="text-lg text-zinc-500 font-medium">Integrate powerful SMS capabilities directly into your
                application using our developer-friendly API.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-12">
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <nav class="sticky top-10 space-y-1">
                    <a href="#authentication"
                        class="block py-2 text-sm font-bold text-indigo-600 hover:text-indigo-700">Authentication</a>
                    <a href="#send-sms" class="block py-2 text-sm font-bold text-zinc-500 hover:text-zinc-900">Send
                        SMS</a>
                    <a href="#response-codes"
                        class="block py-2 text-sm font-bold text-zinc-500 hover:text-zinc-900">Response Codes</a>
                </nav>
            </div>

            <!-- Content Area -->
            <div class="lg:col-span-3 space-y-20">

                <!-- Authentication -->
                <section id="authentication">
                    <h2 class="text-2xl font-bold text-zinc-900 mb-6 flex items-center gap-3">
                        <i data-lucide="key-round" class="w-6 h-6 text-indigo-600"></i>
                        Authentication
                    </h2>
                    <div class="prose prose-zinc max-w-none">
                        <p class="text-zinc-600 font-medium mb-4">
                            All API requests must be authenticated using an API Key. You can manage your keys in the
                            <a href="{{ route('developer.api-keys') }}" class="text-indigo-600 hover:underline">API
                                Settings</a> page.
                        </p>
                        <p class="text-zinc-600 font-medium">
                            Include your key in the <code>X-API-KEY</code> header for every request:
                        </p>
                        <div class="bg-zinc-900 rounded-xl p-4 mt-4 font-mono text-xs text-white overflow-x-auto">
                            X-API-KEY: your_api_key_here
                        </div>
                    </div>
                </section>

                <!-- Send SMS Endpoint -->
                <section id="send-sms">
                    <h2 class="text-2xl font-bold text-zinc-900 mb-6 flex items-center gap-3">
                        <i data-lucide="send" class="w-6 h-6 text-indigo-600"></i>
                        Send SMS
                    </h2>

                    <div class="mb-8">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="px-2 py-1 bg-emerald-500 text-white text-[10px] font-bold rounded">POST</span>
                            <code class="text-sm font-bold text-zinc-800">/api/v1/sms</code>
                        </div>
                        <p class="text-zinc-600 font-medium">Send a plain text message to a single recipient.</p>
                    </div>

                    <h3 class="text-sm font-bold text-zinc-900 uppercase tracking-wider mb-4">Body Parameters</h3>
                    <div class="border border-zinc-200 rounded-xl overflow-hidden mb-8">
                        <table class="w-full text-left text-sm">
                            <thead
                                class="bg-zinc-50 border-b border-zinc-200 font-bold text-zinc-500 text-[10px] uppercase">
                                <tr>
                                    <th class="px-4 py-3">Parameter</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Required</th>
                                    <th class="px-4 py-3">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                <tr>
                                    <td class="px-4 py-4 font-mono text-xs font-bold text-indigo-600">sender_id</td>
                                    <td class="px-4 py-4 text-zinc-500">string</td>
                                    <td class="px-4 py-4 text-rose-500 font-bold">Yes</td>
                                    <td class="px-4 py-4 text-zinc-600 font-medium">Your approved Sender ID/Name.</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-4 font-mono text-xs font-bold text-indigo-600">recipient</td>
                                    <td class="px-4 py-4 text-zinc-500">string</td>
                                    <td class="px-4 py-4 text-rose-500 font-bold">Yes</td>
                                    <td class="px-4 py-4 text-zinc-600 font-medium">International format (e.g.,
                                        +233241234567).</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-4 font-mono text-xs font-bold text-indigo-600">message</td>
                                    <td class="px-4 py-4 text-zinc-500">string</td>
                                    <td class="px-4 py-4 text-rose-500 font-bold">Yes</td>
                                    <td class="px-4 py-4 text-zinc-600 font-medium">Text content (max 480 characters).
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h3 class="text-sm font-bold text-zinc-900 uppercase tracking-wider mb-4">Code Example</h3>
                    <div class="bg-zinc-900 rounded-xl overflow-hidden">
                        <div class="px-4 py-2 bg-zinc-800 border-b border-zinc-700 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">cURL
                                Request</span>
                            <button class="text-zinc-400 hover:text-white transition-colors">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        <pre class="p-6 text-xs text-indigo-300 font-mono overflow-x-auto whitespace-pre-wrap">curl -X POST {{ url('/api/v1/sms') }} \
  -H "X-API-KEY: YOUR_KEY_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "sender_id": "BulkSMS",
    "recipient": "+233241234567",
    "message": "Hello from the API!"
  }'</pre>
                    </div>
                </section>

                <!-- Response Codes -->
                <section id="response-codes">
                    <h2 class="text-2xl font-bold text-zinc-900 mb-6 flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-6 h-6 text-indigo-600"></i>
                        Response Codes
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-start gap-4 p-4 rounded-xl border border-emerald-100 bg-emerald-50">
                            <span class="font-bold text-emerald-600 text-sm">200</span>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-emerald-900">Success</p>
                                <p class="text-xs text-emerald-700 mt-1 font-medium">Message has been queued
                                    successfully.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-xl border border-rose-100 bg-rose-50">
                            <span class="font-bold text-rose-600 text-sm">401</span>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-rose-900">Unauthorized</p>
                                <p class="text-xs text-rose-700 mt-1 font-medium">Invalid or missing API key.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-xl border border-amber-100 bg-amber-50">
                            <span class="font-bold text-amber-600 text-sm">402</span>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-amber-900">Insufficient Balance</p>
                                <p class="text-xs text-amber-700 mt-1 font-medium">Not enough credits to send the
                                    message.</p>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
    <script>
        document.addEventListener('livewire:initialized', () => lucide.createIcons());
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
    </script>
</x-layouts::app.sidebar>