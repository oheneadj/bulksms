<x-layouts::auth.modern>
    <!-- Left Section: Form -->
    <div class="flex-1 flex flex-col p-8 md:px-20 lg:px-32 py-12" x-cloak x-data="{
        showRecoveryInput: @js($errors->has('recovery_code')),
        code: '',
        recovery_code: '',
        toggleInput() {
            this.showRecoveryInput = !this.showRecoveryInput;
            this.code = '';
            this.recovery_code = '';
            $nextTick(() => {
                this.showRecoveryInput
                    ? this.$refs.recovery_code?.focus()
                    : this.$refs.code?.focus();
            });
        },
    }">
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
            <header class="mb-10">
                <template x-if="!showRecoveryInput">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2 tracking-tight">{{ __('Secure access') }}</h1>
                        <p class="text-gray-500 leading-relaxed">
                            {{ __('Enter the authentication code provided by your authenticator application.') }}
                        </p>
                    </div>
                </template>
                <template x-if="showRecoveryInput">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2 tracking-tight">{{ __('Recovery code') }}</h1>
                        <p class="text-gray-500 leading-relaxed">
                            {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
                        </p>
                    </div>
                </template>
            </header>

            <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-8">
                @csrf

                <div>
                    <div x-show="!showRecoveryInput">
                        <label for="code"
                            class="block text-sm font-medium text-gray-700 mb-4">{{ __('Authentication Code') }}</label>
                        <input type="text" name="code" id="code" x-model="code" x-ref="code"
                            class="block w-full text-center tracking-[0.5em] text-2xl font-bold rounded-lg border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 py-5 transition-all text-gray-900 placeholder:text-gray-200"
                            placeholder="XXXXXX" maxlength="6" autofocus />
                        @error('code') <p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="showRecoveryInput" x-cloak>
                        <label for="recovery_code"
                            class="block text-sm font-medium text-gray-700 mb-4">{{ __('Recovery Code') }}</label>
                        <input type="text" name="recovery_code" id="recovery_code" x-ref="recovery_code"
                            x-bind:required="showRecoveryInput" autocomplete="one-time-code" x-model="recovery_code"
                            placeholder="Emergency recovery code"
                            class="block w-full rounded-lg border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-sm py-4 px-5 transition-all text-gray-900 font-medium placeholder:text-gray-300" />
                        @error('recovery_code') <p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-4">
                    <button type="submit"
                        class="w-full bg-[#612fff] text-white font-bold py-4 rounded-[6px] hover:bg-indigo-brand-hover active:scale-[0.99] transition-all shadow-lg shadow-indigo-500/20">
                        {{ __('Continue') }}
                    </button>

                    <button type="button" @click="toggleInput()"
                        class="w-full text-gray-400 text-sm font-bold hover:text-[#612fff] flex items-center justify-center gap-2 transition-colors py-2">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span x-show="!showRecoveryInput">{{ __('Use a recovery code') }}</span>
                        <span x-show="showRecoveryInput">{{ __('Use an authentication code') }}</span>
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
                <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center text-[#612fff] mb-6">
                    <i data-lucide="shield-check" class="w-10 h-10"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('Account Protection') }}</h3>
                <p class="text-gray-600 leading-relaxed">
                    {{ __('Two-factor authentication adds an extra layer of security to your account by requiring more than just a password to log in.') }}
                </p>
            </div>
        </div>
    </div>
</x-layouts::auth.modern>