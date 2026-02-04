<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
    <title>{{ config('app.name') }} | The premium SMS & WhatsApp platform</title>
</head>

<body class="bg-white min-h-screen font-sans antialiased text-zinc-900">
    <!-- Top Banner -->
    <div class="bg-zinc-900 text-white py-2.5 px-4 text-center text-xs flex items-center justify-center gap-3">
        <span class="bg-zinc-700 px-2 py-0.5 rounded-[4px] text-[10px] font-bold uppercase tracking-wider">Launch
            Offer</span>
        <p class="font-medium">Get 100 free SMS credits on your first registration.</p>
        <a href="{{ route('register') }}"
            class="bg-white text-black px-3 py-1 rounded-[6px] text-xs font-bold hover:bg-gray-100 transition-all">Claim
            credits</a>
    </div>

    <!-- Header -->
    <header class="max-w-[1440px] mx-auto px-6 md:px-20 py-5 flex items-center justify-between">
        <div class="flex items-center gap-10">
            <a href="/" class="flex items-center gap-2">
                <x-app-logo-icon class="size-8 fill-current text-indigo-brand" />
                <span class="text-xl font-bold tracking-tight">{{ config('app.name') }}</span>
            </a>

            <nav class="hidden lg:flex items-center gap-8 text-sm font-semibold text-zinc-500">
                <a href="#" class="hover:text-indigo-brand transition-colors">SMS Marketing</a>
                <a href="#" class="hover:text-indigo-brand transition-colors">WhatsApp Business</a>
                <a href="#" class="hover:text-indigo-brand transition-colors">API Keys</a>
                <a href="#" class="hover:text-indigo-brand transition-colors">Pricing</a>
            </nav>
        </div>

        <div class="flex items-center gap-6">
            @auth
                <div class="flex items-center gap-6">
                    <a href="{{ route('dashboard') }}"
                        class="text-zinc-600 hover:text-indigo-brand font-bold text-sm transition-all">{{ __('Dashboard') }}</a>
                    <a href="{{ route('profile.edit') }}"
                        class="text-indigo-brand hover:opacity-80 font-bold text-sm transition-all underline underline-offset-4">{{ __('Profile') }}</a>
                </div>
            @else
                <a href="{{ route('login') }}"
                    class="text-zinc-600 hover:text-indigo-brand font-bold text-sm transition-all">Log in</a>
                <a href="{{ route('register') }}"
                    class="bg-indigo-brand text-white px-5 py-2.5 rounded-[6px] font-bold text-sm hover:bg-indigo-brand-hover transition-all">Get
                    started</a>
            @endauth
        </div>
    </header>

    <main class="max-w-[1440px] mx-auto">
        <!-- Hero Section -->
        <section class="px-6 md:px-20 lg:px-32 py-16 md:py-28 flex flex-col lg:flex-row items-center gap-16">
            <div class="flex-1 max-w-2xl">
                <h1 class="text-2xl md:text-[64px] font-extrabold text-zinc-900 mb-8 leading-[1.05] tracking-tight">
                    The global <span class="text-indigo-400">messaging</span> and <span
                        class="text-indigo-brand">engagement</span> platform for growing businesses
                </h1>
                <p class="text-zinc-500 text-lg mb-10 leading-relaxed max-w-lg font-medium">
                    Send high-conversion SMS and WhatsApp campaigns at scale. Manage your audience, automate responses,
                    and track engagement with developer-friendly APIs.
                </p>

                <div class="flex items-center gap-2 text-indigo-brand font-bold mb-12 group cursor-pointer">
                    <span>Explore SMS solutions</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4 transition-transform group-hover:translate-x-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </div>

                <div
                    class="flex flex-col sm:flex-row gap-0 items-center max-w-lg border border-zinc-200 rounded-[6px] overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500/20 transition-all">
                    <input type="email" placeholder="What's your business email?"
                        class="flex-1 px-5 py-4 bg-white outline-none text-sm font-medium text-zinc-900">
                    <button
                        class="bg-indigo-brand text-white px-8 py-4 font-bold hover:bg-indigo-brand-hover transition-all whitespace-nowrap">
                        Sign up free
                    </button>
                </div>

                <div class="flex items-center gap-2 mt-6">
                    <div class="size-4 bg-indigo-brand/10 rounded flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-2.5 text-indigo-brand">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <p class="text-[10px] text-zinc-500 font-medium uppercase tracking-wider">No credit card required to
                        start</p>
                </div>
            </div>

            <!-- Hero Image Mockup -->
            <div class="flex-1 relative w-full">
                <div class="relative z-10 bg-white border border-zinc-200 rounded-xl overflow-hidden shadow-200/50">
                    <div class="p-6 border-b border-zinc-50 flex items-center justify-between bg-zinc-50/50">
                        <div class="flex items-center gap-2">
                            <div class="size-2.5 rounded-full bg-red-400"></div>
                            <div class="size-2.5 rounded-full bg-yellow-400"></div>
                            <div class="size-2.5 rounded-full bg-green-400"></div>
                        </div>
                    </div>
                    <div class="p-8 bg-white">
                        <div class="space-y-8">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-4">
                                    <div class="size-10 bg-indigo-brand rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="text-white size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-zinc-900">Global Messaging</p>
                                        <p class="text-[10px] text-zinc-500">Reach 200+ countries instantly</p>
                                    </div>
                                </div>
                                <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded">System
                                    Active</span>
                            </div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="p-4 bg-zinc-50 rounded-xl border border-zinc-100">
                                    <p class="text-[10px] text-zinc-500 uppercase font-bold tracking-widest mb-1">
                                        Messages Sent</p>
                                    <p class="text-xl font-extrabold text-zinc-900">842,500</p>
                                </div>
                                <div class="p-4 bg-indigo-brand rounded-xl">
                                    <p class="text-[10px] text-white/70 uppercase font-bold tracking-widest mb-1">
                                        Success Rate</p>
                                    <p class="text-xl font-extrabold text-white">99.8%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating Stats -->
                <div
                    class="absolute -right-8 -bottom-8 w-64 bg-white border border-zinc-200 shadow-200/50 rounded-xl p-6 z-20">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-xs font-bold text-zinc-900">Engagement</p>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4 text-indigo-brand">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-[10px] font-medium">
                            <span class="text-zinc-500">Read Receipts</span>
                            <span class="text-zinc-900 font-bold">84%</span>
                        </div>
                        <div class="h-1.5 bg-zinc-100 rounded-full overflow-hidden">
                            <div class="w-[84%] h-full bg-indigo-brand"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Partner Section -->
        <section class="px-6 md:px-20 py-12 border-y border-zinc-50">
            <div class="flex flex-wrap justify-center items-center gap-12 md:gap-24 opacity-30 grayscale contrast-125">
                <span class="font-bold text-2xl tracking-tighter">HUBSPOT</span>
                <span class="font-bold text-2xl tracking-tighter">TWILIO</span>
                <span class="font-bold text-2xl tracking-tighter">MESSAGEBIRD</span>
                <span class="font-bold text-2xl tracking-tighter">STRIPE</span>
                <span class="font-bold text-2xl tracking-tighter">SHOPIFY</span>
            </div>
        </section>

        <!-- Features Grid -->
        <section class="px-6 md:px-20 py-24 bg-zinc-50/30">
            <div class="text-center mb-16">
                <p class="text-indigo-brand font-bold text-xs uppercase tracking-[0.2em] mb-4">Powerful Features</p>
                <h2 class="text-3xl md:text-4xl font-extrabold text-zinc-900 mb-6">Engineered for global reach</h2>
                <p class="text-zinc-500 max-w-2xl mx-auto leading-relaxed">Everything you need to engage your customers
                    effectively, whether you're a startup or an enterprise scale business.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    class="bg-white p-8 rounded-[12px] border border-zinc-100 transition-all hover:-translate-y-2 hover:border-indigo-500/30 shadow-sm">
                    <div class="size-10 bg-orange-100 rounded-lg flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="text-orange-600 size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-zinc-900 mb-3 text-lg">Bulk SMS</h3>
                    <p class="text-sm text-zinc-500 leading-relaxed">Blast SMS campaigns to thousands of contacts with
                        one click.</p>
                </div>

                <div
                    class="bg-white p-8 rounded-xl border border-zinc-200 shadow-200/50 transition-all hover:-translate-y-2 hover:border-indigo-500/30">
                    <div class="size-10 bg-indigo-100 rounded-lg flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="text-indigo-brand size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-zinc-900 mb-3 text-lg">WhatsApp API</h3>
                    <p class="text-sm text-zinc-500 leading-relaxed">Rich media messages, interactive buttons, and
                        session chats.</p>
                </div>

                <div
                    class="bg-white p-8 rounded-xl border border-zinc-200 shadow-200/50 transition-all hover:-translate-y-2 hover:border-indigo-500/30">
                    <div class="size-10 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="text-blue-600 size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-zinc-900 mb-3 text-lg">Contact CRM</h3>
                    <p class="text-sm text-zinc-500 leading-relaxed">Dynamic groups, custom fields, and automated
                        audience segments.</p>
                </div>

                <div
                    class="bg-white p-8 rounded-xl border border-zinc-200 shadow-200/50 transition-all hover:-translate-y-2 hover:border-indigo-500/30">
                    <div class="size-10 bg-teal-100 rounded-lg flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="text-teal-600 size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-zinc-900 mb-3 text-lg">Automations</h3>
                    <p class="text-sm text-zinc-500 leading-relaxed">Schedule recurring messages and set up event-based
                        triggers.</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-zinc-900 text-white py-12 px-6 md:px-20 mt-20">
        <div class="flex flex-col md:flex-row justify-between items-center gap-8 max-w-[1440px] mx-auto">
            <div class="flex items-center gap-2">
                <x-app-logo-icon class="size-8 fill-current text-indigo-400" />
                <span class="text-xl font-bold tracking-tight">{{ config('app.name') }}</span>
            </div>
            <div class="flex items-center gap-8">
                @auth
                    <a href="{{ route('profile.edit') }}"
                        class="text-zinc-500 hover:text-indigo-400 text-xs font-bold transition-all">{{ __('My Account') }}</a>
                @endauth
                <p class="text-zinc-500 text-xs uppercase tracking-widest">© {{ date('Y') }} {{ config('app.name') }}.
                    Built for reliability.</p>
            </div>
        </div>
    </footer>
</body>

</html>