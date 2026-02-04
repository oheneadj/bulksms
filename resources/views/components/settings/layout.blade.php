<div class="flex items-start max-md:flex-col gap-12">
    <div class="w-full md:w-[240px] shrink-0">
        <nav aria-label="{{ __('Settings') }}" class="flex flex-col space-y-1">
            <a href="{{ route('profile.edit') }}" wire:navigate
                class="flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg transition-colors {{ request()->routeIs('profile.edit') ? 'bg-indigo-50 text-indigo-brand' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                {{ __('Profile Information') }}
            </a>

            <a href="{{ route('user-password.edit') }}" wire:navigate
                class="flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg transition-colors {{ request()->routeIs('user-password.edit') ? 'bg-indigo-50 text-indigo-brand' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                {{ __('Security & Password') }}
            </a>

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <a href="{{ route('two-factor.show') }}" wire:navigate
                    class="flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg transition-colors {{ request()->routeIs('two-factor.show') ? 'bg-indigo-50 text-indigo-brand' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                    {{ __('Two-Factor Auth') }}
                </a>
            @endif

            <a href="{{ route('appearance.edit') }}" wire:navigate
                class="flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg transition-colors {{ request()->routeIs('appearance.edit') ? 'bg-indigo-50 text-indigo-brand' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                {{ __('System Appearance') }}
            </a>
        </nav>
    </div>

    <div
        class="flex-1 max-w-2xl bg-white rounded-xl p-8 border border-zinc-200/50 shadow-200/50 overflow-hidden min-h-[500px]">
        <div class="mb-10">
            <h2 class="text-xl font-bold text-zinc-900 tracking-tight">{{ $heading ?? '' }}</h2>
            <p class="text-sm text-zinc-500 font-medium mt-1">{{ $subheading ?? '' }}</p>
        </div>

        <div class="w-full">
            {{ $slot }}
        </div>
    </div>
</div>