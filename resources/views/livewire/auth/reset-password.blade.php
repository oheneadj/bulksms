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

        <div class="max-w-md w-full mx-auto" x-data="{
                password: '',
                showPass: false,
                showConfirm: false,
                validation: {
                    length: false,
                    upper: false,
                    lower: false,
                    number: false
                },
                updateValidation() {
                    this.validation.length = this.password.length >= 8;
                    this.validation.upper = /[A-Z]/.test(this.password);
                    this.validation.lower = /[a-z]/.test(this.password);
                    this.validation.number = /[0-9!@#$%^&*()_+\-=\[\]{};':&quot;\\|,.<>\/?]/.test(this.password);
                }
            }">
            <h1 class="text-3xl font-bold text-gray-900 mb-2 tracking-tight">{{ __('Reset password') }}</h1>
            <p class="text-gray-500 mb-10">{{ __('Enter your new password below.') }}</p>


            <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                <!-- Token -->
                <input type="hidden" name="token" value="{{ request()->route('token') }}">

                <div>
                    <label for="email"
                        class="block text-sm font-medium text-gray-700 mb-2">{{ __('Business email') }}</label>
                    <input type="email" name="email" id="email" value="{{ request('email') }}" required
                        autocomplete="email"
                        class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900 @error('email') border-red-500 @enderror">
                    @error('email') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="relative group/pass" x-data="{
                    password: '',
                    showPass: false,
                    showConfirm: false,
                    validation: {
                        length: false,
                        upper: false,
                        lower: false,
                        number: false
                    },
                    updateValidation() {
                        this.validation.length = this.password.length >= 8;
                        this.validation.upper = /[A-Z]/.test(this.password);
                        this.validation.lower = /[a-z]/.test(this.password);
                        this.validation.number = /[0-9!@#$%^&*()_+\-=\[\] {};':\&quot;\\|,.<>\/?]/.test(this.password);
                    }
                }">
                    <label for="password"
                        class="block text-sm font-medium text-gray-700 mb-2">{{ __('New password') }}</label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="password" id="password" required
                            autocomplete="new-password" x-model="password" @input="updateValidation()"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900 @error('password') border-red-500 @enderror">
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                            @click="showPass = !showPass">
                            <i data-lucide="eye" x-show="!showPass" class="w-5 h-5"></i>
                            <i data-lucide="eye-off" x-show="showPass" class="w-5 h-5" x-cloak></i>
                        </button>
                    </div>
                    @error('password') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror

                    <!-- Validation Tooltip -->
                    <div
                        class="absolute left-0 top-full mt-2 w-full bg-white border border-gray-100 rounded-xl shadow-xl p-6 z-30 opacity-0 pointer-events-none transition-all transform translate-y-2 group-focus-within/pass:opacity-100 group-focus-within/pass:translate-y-0 max-w-[320px]">
                        <p class="text-[11px] font-bold text-gray-900 mb-4 uppercase tracking-wider">
                            {{ __('Your password must have:') }}
                        </p>
                        <ul class="space-y-2">
                            <li class="flex items-center gap-2 transition-colors"
                                :class="validation.length ? 'text-green-500' : 'text-gray-400'">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span class="text-[11px] font-medium">{{ __('At least 8 characters long') }}</span>
                            </li>
                            <li class="flex items-center gap-2 transition-colors"
                                :class="validation.upper ? 'text-green-500' : 'text-gray-400'">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span class="text-[11px] font-medium">{{ __('One Uppercase character') }}</span>
                            </li>
                            <li class="flex items-center gap-2 transition-colors"
                                :class="validation.lower ? 'text-green-500' : 'text-gray-400'">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span class="text-[11px] font-medium">{{ __('One Lowercase character') }}</span>
                            </li>
                            <li class="flex items-center gap-2 transition-colors"
                                :class="validation.number ? 'text-green-500' : 'text-gray-400'">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span class="text-[11px] font-medium">{{ __('One number or symbol') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation"
                        class="block text-sm font-medium text-gray-700 mb-2">{{ __('Confirm password') }}</label>
                    <div class="relative">
                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation"
                            id="password_confirmation" required autocomplete="new-password"
                            class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-gray-900">
                        <button type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                            @click="showConfirm = !showConfirm">
                            <i data-lucide="eye" x-show="!showConfirm" class="w-5 h-5"></i>
                            <i data-lucide="eye-off" x-show="showConfirm" class="w-5 h-5" x-cloak></i>
                        </button>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-[#612fff] text-white font-bold py-4 rounded-[6px] hover:bg-indigo-brand-hover active:scale-[0.99] transition-all shadow-lg shadow-indigo-500/20">
                        {{ __('Reset password') }}
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
                class="bg-white rounded-xl overflow-hidden border border-zinc-200 shadow-200/50 p-12 flex flex-col items-center text-center">
                <div
                    class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center text-blue-600 mb-6 font-bold text-2xl">
                    100%
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('Account recovered') }}</h3>
                <p class="text-gray-600 leading-relaxed">
                    {{ __('Once you\'ve reset your password, you\'ll have full access to your dashboard and all features.') }}
                </p>
            </div>
        </div>
    </div>
</x-layouts::auth.modern>