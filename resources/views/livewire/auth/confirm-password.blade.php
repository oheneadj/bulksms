<x-layouts::auth.modern>
    <!-- Left Section: Form -->
    <div class="flex-1 flex flex-col p-8 md:px-20 lg:px-32 py-12">
        <div class="mb-20">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-primary">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="#612fff" />
                    <path d="M2 17L12 22L22 17" stroke="#612fff" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M2 12L12 17L22 12" stroke="#612fff" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                <span class="text-2xl font-bold text-gray-900 tracking-tight">{{ config('app.name') }}</span>
            </a>
        </div>

        <div class="max-w-md w-full mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-2 tracking-tight">{{ __('Confirm password') }}</h1>
            <p class="text-gray-500 mb-10">
                {{ __('This is a secure area. Please confirm your password before continuing.') }}
            </p>


            <form method="POST" action="{{ route('password.confirm.store') }}" class="space-y-6"
                x-data="{ show: false }">
                @csrf

                <div>
                    <label for="password"
                        class="block text-sm font-medium text-gray-700 mb-2">{{ __('Password') }}</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" id="password" required
                            autocomplete="current-password" autofocus placeholder="Your password"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900 @error('password') border-red-500 @enderror">
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                            @click="show = !show">
                            <i data-lucide="eye" x-show="!show" class="w-5 h-5"></i>
                            <i data-lucide="eye-off" x-show="show" class="w-5 h-5" x-cloak></i>
                        </button>
                    </div>
                    @error('password') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-[#612fff] text-white font-bold py-4 rounded-[6px] hover:bg-indigo-brand-hover active:scale-[0.99] transition-all shadow-lg shadow-indigo-500/20">
                        {{ __('Confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Section: Decorative -->
    <div class="hidden md:flex flex-1 bg-gray-50 items-center justify-center p-12 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-indigo-100/50 rounded-full blur-3xl -mr-96 -mt-96">
        </div>
        <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-blue-50 rounded-full blur-3xl -ml-48 -mb-48"></div>

        <div class="relative z-10 max-w-lg">
            <div
                class="rounded-xl overflow-hidden border border-zinc-200 shadow-200/50 p-12 flex flex-col items-center text-center bg-white">
                <div
                    class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center text-[#612fff] mb-6 font-bold">
                    <i data-lucide="shield-check" class="w-10 h-10"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('Enhanced Security') }}</h3>
                <p class="text-gray-600 leading-relaxed">
                    {{ __('We require a password confirmation for sensitive actions to ensure you\'re the one making the changes.') }}
                </p>
            </div>
        </div>
    </div>
</x-layouts::auth.modern>