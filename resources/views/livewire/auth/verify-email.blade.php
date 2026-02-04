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
            <h1 class="text-3xl font-bold text-gray-900 mb-2 tracking-tight">{{ __('Verify email') }}</h1>
            <p class="text-gray-500 mb-10">
                {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
            </p>


            <div class="space-y-8">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit"
                        class="w-full bg-[#612fff] text-white font-bold py-4 rounded-[6px] hover:bg-indigo-brand-hover active:scale-[0.99] transition-all shadow-lg shadow-indigo-500/20">
                        {{ __('Resend verification email') }}
                    </button>
                </form>

                <div class="flex items-center justify-between pt-8 border-t border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-gray-500 text-sm font-bold hover:text-[#612fff] flex items-center gap-2">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            {{ __('Log out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Section: Testimonial -->
    <div class="hidden md:flex flex-1 bg-gray-50 items-center justify-center p-12 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-indigo-100/50 rounded-full blur-3xl -mr-96 -mt-96">
        </div>
        <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-blue-50 rounded-full blur-3xl -ml-48 -mb-48"></div>

        <div class="relative z-10 max-w-lg">
            <div
                class="rounded-xl overflow-hidden border border-zinc-200 shadow-200/50 p-12 flex flex-col items-center text-center bg-white">
                <div
                    class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center text-green-600 mb-6 font-bold text-2xl">
                    <i data-lucide="mail-check" class="w-8 h-8"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('Almost there!') }}</h3>
                <p class="text-gray-600 leading-relaxed">
                    {{ __('Check your inbox and confirm your email. This helps us keep your account secure and allows you to start using all our features.') }}
                </p>
            </div>
        </div>
    </div>
</x-layouts::auth.modern>