<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">

<head>
    @include('partials.head')
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex font-sans antialiased text-zinc-900">
    <!-- Admin Sidebar -->
    <aside class="w-64 bg-zinc-900 text-white flex flex-col fixed h-full z-20 border-r border-red-900/30">
        <div class="p-6">
            <div class="flex items-center gap-2 mb-8 px-2">
                <div class="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center shadow-lg shadow-red-500/20">
                    <x-app-logo-icon class="text-white w-5 h-5 fill-current" />
                </div>
                <div>
                    <h2 class="text-white font-bold leading-tight tracking-tight">Super Admin</h2>
                    <p class="text-[9px] uppercase tracking-[0.2em] font-extrabold text-red-500">System Control</p>
                </div>
            </div>

            <nav class="space-y-1 mb-8">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white' : 'hover:bg-white/5 text-gray-400 hover:text-white' }}">
                    <i data-lucide="layout-dashboard"
                        class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-red-500' : '' }}"></i>
                    <span class="text-sm font-semibold">Dashboard</span>
                </a>

                <a href="{{ route('admin.tenants') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.tenants') ? 'bg-white/10 text-white' : 'hover:bg-white/5 text-gray-400 hover:text-white' }}">
                    <i data-lucide="building-2"
                        class="w-4 h-4 {{ request()->routeIs('admin.tenants') ? 'text-red-500' : '' }}"></i>
                    <span class="text-sm font-semibold">Tenants</span>
                </a>

                <a href="{{ route('admin.providers.index') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.providers.*') ? 'bg-white/10 text-white' : 'hover:bg-white/5 text-gray-400 hover:text-white' }}">
                    <i data-lucide="router"
                        class="w-4 h-4 {{ request()->routeIs('admin.providers.*') ? 'text-red-500' : '' }}"></i>
                    <span class="text-sm font-semibold">SMS Providers</span>
                </a>

                <a href="{{ route('admin.packages') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group {{ request()->routeIs('admin.packages') ? 'bg-white/10 text-white' : 'hover:bg-white/5 text-gray-400 hover:text-white' }}">
                    <i data-lucide="package"
                        class="w-4 h-4 {{ request()->routeIs('admin.packages') ? 'text-red-500' : '' }}"></i>
                    <span class="text-sm font-semibold">Credit Packages</span>
                </a>
            </nav>

            <div class="mt-auto pt-6 border-t border-white/10">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all duration-200 group hover:bg-white/5 text-gray-400 hover:text-white">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span class="text-sm font-semibold">Exit to App</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 ml-64 min-h-screen flex flex-col">
        <header
            class="h-16 flex items-center justify-between px-8 bg-white/90 backdrop-blur-md sticky top-0 z-10 border-b border-zinc-200/50">
            <div class="flex items-center gap-4">
                <h2 class="text-lg font-bold text-zinc-900">{{ $title ?? 'Admin' }}</h2>
            </div>

            <div class="flex items-center gap-6">
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.outside="open = false"
                        class="flex items-center gap-3 pl-6 border-l border-zinc-100 group hover:opacity-80 transition-all outline-none">
                        <div class="flex flex-col items-end hidden sm:flex">
                            <span
                                class="text-sm font-bold text-gray-900 leading-none mb-1 group-hover:text-red-500 transition-colors">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] font-extrabold text-red-500 uppercase tracking-wider">Super
                                Admin</span>
                        </div>
                        <div
                            class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs font-bold transition-transform group-hover:scale-105">
                            SA
                        </div>
                    </button>

                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-zinc-100 py-2 z-50 origin-top-right">

                        <!-- Menu Items -->
                        <div class="py-1">
                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 font-medium transition-colors">
                                <i data-lucide="user" class="w-4 h-4 text-zinc-400"></i>
                                Profile & Settings
                            </a>
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

        <main class="flex-1 p-10 lg:p-12">
            <div class="max-w-[1600px] mx-auto">
                {{ $slot }}
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
    {!! \Devrabiul\ToastMagic\Facades\ToastMagic::scripts() !!}
</body>

</html>