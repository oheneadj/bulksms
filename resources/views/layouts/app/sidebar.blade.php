<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">

<head>
    @include('partials.head')
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .sidebar-item-active {
            background-color: rgba(255, 255, 255, 0.05);
            color: #612fff;
        }

        .sidebar-item:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }
    </style>
</head>

<body class="bg-gray-200 min-h-screen flex font-sans antialiased text-zinc-900">
    <!-- Sidebar -->
    <aside class="w-64 bg-zinc-900 text-gray-400 flex flex-col fixed h-full z-20">
        <div class="flex-1 overflow-y-auto p-6 scrollbar-thin scrollbar-thumb-zinc-800 scrollbar-track-transparent">
            <div class="flex items-center gap-2 mb-8 px-2">
                <div
                    class="w-8 h-8 rounded-lg bg-indigo-brand flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <x-app-logo-icon class="text-white w-5 h-5 fill-current" />
                </div>
                <div>
                    <h2 class="text-white font-bold leading-tight tracking-tight">{{ config('app.name') }}</h2>
                    <p class="text-[9px] uppercase tracking-[0.2em] font-extrabold opacity-40">Organisation</p>
                </div>
                <button class="ml-auto text-gray-600 hover:text-white transition-colors">
                    <i data-lucide="chevrons-up-down" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Core Section -->
            <nav class="space-y-1 mb-8">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="layout-dashboard"
                        class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">Dashboard</span>
                </a>

                <a href="{{ route('billing') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('billing') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="receipt"
                        class="w-4 h-4 {{ request()->routeIs('billing') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">Billing</span>
                </a>
            </nav>

            <!-- Messaging Section -->
            <div class="mb-2 px-4 text-[10px] font-extrabold text-white/20 uppercase tracking-[0.2em]">
                Messaging
            </div>
            <nav class="space-y-1 mb-8">
                <a href="{{ route('messaging.send') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('messaging.send') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="send"
                        class="w-4 h-4 {{ request()->routeIs('messaging.send') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">Send SMS</span>
                </a>
                <a href="{{ route('messaging.templates') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('messaging.templates') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="layout-template"
                        class="w-4 h-4 {{ request()->routeIs('messaging.templates') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">Templates</span>
                </a>
                <a href="{{ route('messaging.campaigns') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('messaging.campaigns') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="layers"
                        class="w-4 h-4 {{ request()->routeIs('messaging.campaigns') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">Campaigns</span>
                </a>
                <a href="{{ route('messaging.history') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('messaging.history') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="history"
                        class="w-4 h-4 {{ request()->routeIs('messaging.history') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">History</span>
                </a>
                <a href="{{ route('messaging.sender-ids') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('messaging.sender-ids') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="key"
                        class="w-4 h-4 {{ request()->routeIs('messaging.sender-ids') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">Sender IDs</span>
                </a>
            </nav>

            <!-- Audience Section -->
            <div class="mb-2 px-4 text-[10px] font-extrabold text-white/20 uppercase tracking-[0.2em]">
                Audience
            </div>
            <nav class="space-y-1 mb-8">
                <a href="{{ route('contacts') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('contacts*') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="users"
                        class="w-4 h-4 {{ request()->routeIs('contacts*') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">Contacts</span>
                </a>
                <a href="{{ route('groups') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('groups') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="folder-kanban"
                        class="w-4 h-4 {{ request()->routeIs('groups') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">Groups</span>
                </a>
                <a href="{{ route('messaging.birthdays') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('messaging.birthdays') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="cake"
                        class="w-4 h-4 {{ request()->routeIs('messaging.birthdays') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">Birthdays</span>
                </a>
            </nav>

            <!-- Developer Section -->
            {{-- <div class="mb-2 px-4 text-[10px] font-extrabold text-white/20 uppercase tracking-[0.2em]">
                Developer
            </div>
            <nav class="space-y-1 mb-8">
                <a href="{{ route('developer.api-keys') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('developer.api-keys') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="code-2"
                        class="w-4 h-4 {{ request()->routeIs('developer.api-keys') ? 'sidebar-item-active' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">API Keys</span>
                </a>
                <a href="{{ route('developer.docs') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('developer.docs') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="book-open"
                        class="w-4 h-4 {{ request()->routeIs('developer.docs') ? 'sidebar-item-active' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">API Documentation</span>
                </a>
                <a href="{{ route('developer.webhooks') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('developer.webhooks') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="webhook"
                        class="w-4 h-4 {{ request()->routeIs('developer.webhooks') ? 'sidebar-item-active' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">Webhooks</span>
                </a>
            </nav> --}}

            <!-- Account Section -->
            <div class="mb-2 px-4 text-[10px] font-extrabold text-white/20 uppercase tracking-[0.2em]">
                Account
            </div>
            <nav class="space-y-1 mb-8">
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('profile.edit') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                    <i data-lucide="settings"
                        class="w-4 h-4 {{ request()->routeIs('profile.edit') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                    <span class="text-sm font-semibold">Settings</span>
                </a>
                @if(auth()->user()->isTenantAdmin())
                    <a href="{{ route('settings.team') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('settings.team') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                        <i data-lucide="users-2"
                            class="w-4 h-4 {{ request()->routeIs('settings.team') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                        <span class="text-sm font-semibold">Team Members</span>
                    </a>
                @endif
            </nav>

            <!-- Admin Section -->
            @can('super-admin')
                <div class="mt-6 mb-2 px-4 text-[10px] font-extrabold text-white/20 uppercase tracking-[0.2em]">
                    Admin
                </div>
                <nav class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.dashboard') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                        <i data-lucide="layout-dashboard"
                            class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                        <span class="text-sm font-semibold">Dashboard</span>
                    </a>
                    <a href="{{ route('admin.sender-ids') }}"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.sender-ids') ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-white' }}">
                        <i data-lucide="shield-alert"
                            class="w-4 h-4 {{ request()->routeIs('admin.sender-ids') ? 'text-indigo-brand' : 'group-hover:text-indigo-brand' }}"></i>
                        <span class="text-sm font-semibold">Sender ID Approval</span>
                    </a>
                </nav>
            @endcan
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-white/5 shrink-0 bg-zinc-900">
            <div class="flex items-center gap-3 px-2 py-2">
                <div
                    class="w-8 h-8 rounded-lg bg-indigo-brand/10 flex items-center justify-center text-indigo-brand shrink-0">
                    <i data-lucide="help-circle" class="w-5 h-5"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-white leading-tight">Support</span>
                    <span class="text-[10px] text-gray-500 font-medium whitespace-nowrap">bulksms@support.com</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 ml-64 min-h-screen flex flex-col">
        <!-- Top Nav -->
        <header
            class="h-16 flex items-center justify-between px-8 bg-white/90 backdrop-blur-md sticky top-0 z-10 border-b border-zinc-200/50">
            <div class="flex items-center gap-4">
                <button class="md:hidden p-2 text-gray-500 hover:bg-gray-50 rounded-lg">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="hidden md:flex items-center gap-2 text-gray-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span class="text-sm font-medium">Search anything...</span>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <livewire:notifications />

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.outside="open = false"
                        class="flex items-center gap-3 pl-6 border-l border-zinc-100 group hover:opacity-80 transition-all outline-none">
                        <div class="flex flex-col items-end hidden sm:flex">
                            <span
                                class="text-sm font-bold text-gray-900 leading-none mb-1 group-hover:text-indigo-brand transition-colors">{{ auth()->user()->name }}</span>
                            <span
                                class="text-[10px] font-extrabold text-indigo-brand uppercase tracking-wider">{{ auth()->user()->email }}</span>
                        </div>
                        <div
                            class="w-10 h-10 rounded-lg bg-gray-50 border border-zinc-200 flex items-center justify-center text-indigo-brand font-bold text-sm shadow-sm transition-transform group-hover:scale-105 group-hover:border-indigo-brand/20">
                            {{ auth()->user()->initials() }}
                        </div>
                    </button>

                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-zinc-100 py-2 z-50 origin-top-right">

                        <!-- User Role -->
                        <div class="px-4 py-3 border-b border-zinc-50">
                            <span class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-0.5">User
                                Type</span>
                            <span
                                class="block text-xs font-bold text-indigo-600 capitalize bg-indigo-50 px-2 py-1 rounded inline-block">
                                {{ str_replace('_', ' ', auth()->user()->role) }}
                            </span>
                        </div>

                        <!-- Menu Items -->
                        <div class="py-1">
                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 font-medium transition-colors">
                                <i data-lucide="user" class="w-4 h-4 text-zinc-400"></i>
                                Profile
                            </a>

                            @if(Route::has('api-tokens.index'))
                                <a href="{{ route('api-tokens.index') }}"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 font-medium transition-colors">
                                    <i data-lucide="code" class="w-4 h-4 text-zinc-400"></i>
                                    API Tokens
                                </a>
                            @endif
                        </div>

                        <!-- Logout -->
                        <div class="border-t border-zinc-50 mt-1 pt-1">
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf
                                <button type="submit"
                                    class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium transition-colors">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Content -->
        <main class="flex-1 p-10 lg:p-12">
            <div class="max-w-[1600px] mx-auto">
                {{ $slot }}
            </div>
        </main>
    </div>

    <script>
        const initLucide = () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        };

        document.addEventListener('DOMContentLoaded', initLucide);
        document.addEventListener('livewire:navigated', initLucide);

        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', (el, component) => {
                initLucide();
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            @if (session('status'))
                const toast = new ToastMagic();
                let message = "{{ session('status') }}";
                if (message === 'verification-link-sent') {
                    message = "{{ __('A new verification link has been sent to your email address.') }}";
                }
                toast.success(message);
            @endif
        });
    </script>
    {!! \Devrabiul\ToastMagic\Facades\ToastMagic::scripts() !!}
</body>

</html>