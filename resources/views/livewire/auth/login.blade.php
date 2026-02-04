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
            <h1 class="text-3xl font-bold text-gray-900 mb-2 tracking-tight">{{ __('Welcome Back!') }}</h1>
            <p class="text-gray-500 mb-10">{{ __('Log in to continue your journey.') }}</p>


            <form method="POST" action="{{ route('login.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email"
                        class="block text-sm font-medium text-gray-700 mb-2">{{ __('Business email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        autocomplete="email" placeholder="info@forma-advisory.com"
                        class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900 @error('email') border-red-500 @enderror">
                    @error('email') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="relative">
                    <div class="flex items-center justify-between mb-2">
                        <label for="password"
                            class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-xs font-bold text-[#612fff] hover:underline">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" name="password" id="password" required
                            autocomplete="current-password"
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

                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="remember"
                            class="w-4 h-4 rounded border-gray-300 text-[#612fff] focus:ring-[#612fff]/20 transition-all">
                        <span class="text-sm text-gray-500 group-hover:text-gray-700">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-[#612fff] text-white font-bold py-4 rounded-[6px] hover:bg-indigo-brand-hover active:scale-[0.99] transition-all shadow-lg shadow-indigo-500/20">
                        {{ __('Log in') }}
                    </button>
                </div>
            </form>

            <div class="mt-10 flex flex-col items-center">
                <div class="w-full flex items-center gap-4 mb-8">
                    <div class="h-[1px] bg-gray-100 flex-1"></div>
                    <span
                        class="text-[10px] uppercase font-bold text-gray-300 tracking-widest">{{ __('or continue with') }}</span>
                    <div class="h-[1px] bg-gray-100 flex-1"></div>
                </div>

                <div class="flex gap-4">
                    <button
                        class="w-12 h-12 rounded-lg border border-gray-100 bg-white flex items-center justify-center hover:bg-gray-50 transition-all shadow-sm">
                        <x-google-icon class="w-5 h-5" />
                    </button>
                    <button
                        class="w-12 h-12 rounded-lg border border-gray-100 bg-white flex items-center justify-center hover:bg-gray-50 transition-all shadow-sm">
                        <x-apple-icon class="w-5 h-5" />
                    </button>
                    <button
                        class="w-12 h-12 rounded-lg border border-gray-100 bg-white flex items-center justify-center hover:bg-gray-50 transition-all shadow-sm">
                        <x-facebook-icon class="w-5 h-5" />
                    </button>
                </div>

                <div class="mt-12 text-center text-sm text-gray-500">
                    {{ __('Not a member?') }}
                    <a href="{{ route('register') }}" class="text-[#612fff] font-bold hover:underline ml-1">
                        {{ __('Join now') }}
                    </a>
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
            <div class="rounded-xl overflow-hidden shadow-200/50 border border-zinc-200/50">
                <div class="p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="infinity" class="text-white w-6 h-6"></i>
                    </div>
                    <span class="font-bold text-gray-900">bowtie</span>
                </div>
                <div class="relative aspect-video bg-gray-100">
                    <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&q=80&w=1000"
                        alt="Testimonial" class="w-full h-full object-cover grayscale-[0.2]">
                </div>
                <div class="p-8">
                    <p class="text-gray-700 text-lg font-medium leading-relaxed mb-6">
                        "The platform integration has been seamless. We've seen a significant increase in engagement and
                        our teams love the intuitive interface."
                    </p>
                    <div>
                        <h4 class="font-bold text-gray-900 text-lg">Sarah Jenkins</h4>
                        <p class="text-gray-500">Head of Operations at TechFlow</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::auth.modern>