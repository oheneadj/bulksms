<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <header class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 sticky top-0 z-20">
        <div class="max-w-[1440px] mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- Mobile Toggle (Simplified) -->
                <button class="lg:hidden text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2">
                    <x-app-logo-icon class="w-8 h-8 fill-current text-indigo-brand" />
                </a>

                <nav class="hidden lg:flex items-center gap-6 ml-4">
                    <a href="{{ route('dashboard') }}" wire:navigate
                        class="text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
                        {{ __('Dashboard') }}
                    </a>
                    <a href="{{ route('profile.edit') }}" wire:navigate
                        class="text-sm font-medium transition-colors {{ request()->routeIs('profile.edit') ? 'text-zinc-900 dark:text-white' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
                        {{ __('Settings') }}
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <!-- Right Actions -->
                <div class="hidden lg:flex items-center gap-2 mr-2">
                    <a href="#" class="p-2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors"
                        title="{{ __('Search') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </a>
                    <a href="https://github.com/laravel/livewire-starter-kit" target="_blank"
                        class="p-2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors"
                        title="{{ __('Repository') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
                        </svg>
                    </a>
                </div>

                <x-desktop-user-menu />
            </div>
        </div>
    </header>

    <!-- Main Content Slot -->
    {{ $slot }}
</body>

</html>