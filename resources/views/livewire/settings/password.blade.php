<section class="w-full">
    @include('partials.settings-heading')

    <h1 class="sr-only">{{ __('Password Settings') }}</h1>

    <x-settings.layout :heading="__('Security & Password')" :subheading="__('Update your password and ensure your account remains secure.')">
        <form method="POST" wire:submit="updatePassword" class="space-y-6">
            <div>
                <label for="current_password"
                    class="block text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] mb-2 ml-1">{{ __('Current Password') }}</label>
                <input type="password" wire:model="current_password" id="current_password" required
                    autocomplete="current-password" placeholder="••••••••"
                    class="block w-full px-4 py-3.5 bg-gray-50 border border-zinc-200 rounded-lg text-zinc-900 font-bold placeholder:text-zinc-300 focus:ring-4 focus:ring-indigo-brand/5 focus:border-indigo-brand focus:bg-white transition-all outline-none">
                @error('current_password') <p
                    class="mt-1 text-xs text-red-500 font-medium ml-1 flex items-center gap-1">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    {{ $message }}
                </p> @enderror
            </div>

            <div x-data="{ 
                password: '',
                showTooltip: false,
                get length() { return this.password.length >= 8 },
                get upper() { return /[A-Z]/.test(this.password) },
                get lower() { return /[a-z]/.test(this.password) },
                get symbol() { return /[0-9!@#$%^&*(),.?\':{}|<>]/.test(this.password) }
            }" class="relative section-transition">
                <div>
                    <label for="password"
                        class="block text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] mb-2 ml-1">{{ __('New Password') }}</label>
                    <input type="password" wire:model="password" id="password" required autocomplete="new-password"
                        placeholder="••••••••"
                        class="block w-full px-4 py-3.5 bg-gray-50 border border-zinc-200 rounded-lg text-zinc-900 font-bold placeholder:text-zinc-300 focus:ring-4 focus:ring-indigo-brand/5 focus:border-indigo-brand focus:bg-white transition-all outline-none"
                        x-model="password" @focus="showTooltip = true" @blur="showTooltip = false">
                    @error('password') <p class="mt-1 text-xs text-red-500 font-medium ml-1 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        {{ $message }}
                    </p> @enderror
                </div>

                <!-- Validation Tooltip -->
                <div x-show="showTooltip" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="absolute z-20 left-0 top-full mt-3 w-full p-6 bg-white border border-zinc-200/50 rounded-xl shadow-200/50 pointer-events-none"
                    x-cloak>

                    <h3 class="text-[10px] font-extrabold text-zinc-900 uppercase tracking-[0.1em] mb-4">
                        {{ __('New password requirements:') }}
                    </h3>

                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <li class="flex items-center gap-3 text-xs font-bold transition-all duration-300"
                            :class="length ? 'text-emerald-600' : 'text-zinc-400'">
                            <div class="size-5 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                                :class="length ? 'bg-emerald-50 border-emerald-100' : 'bg-zinc-50 border-zinc-100'">
                                <i data-lucide="check" class="size-3 transition-opacity"
                                    :class="length ? 'opacity-100' : 'opacity-0'"></i>
                            </div>
                            {{ __('8+ characters') }}
                        </li>
                        <li class="flex items-center gap-3 text-xs font-bold transition-all duration-300"
                            :class="upper ? 'text-emerald-600' : 'text-zinc-400'">
                            <div class="size-5 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                                :class="upper ? 'bg-emerald-50 border-emerald-100' : 'bg-zinc-50 border-zinc-100'">
                                <i data-lucide="check" class="size-3 transition-opacity"
                                    :class="upper ? 'opacity-100' : 'opacity-0'"></i>
                            </div>
                            {{ __('Uppercase') }}
                        </li>
                        <li class="flex items-center gap-3 text-xs font-bold transition-all duration-300"
                            :class="lower ? 'text-emerald-600' : 'text-zinc-400'">
                            <div class="size-5 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                                :class="lower ? 'bg-emerald-50 border-emerald-100' : 'bg-zinc-50 border-zinc-100'">
                                <i data-lucide="check" class="size-3 transition-opacity"
                                    :class="lower ? 'opacity-100' : 'opacity-0'"></i>
                            </div>
                            {{ __('Lowercase') }}
                        </li>
                        <li class="flex items-center gap-3 text-xs font-bold transition-all duration-300"
                            :class="symbol ? 'text-emerald-600' : 'text-zinc-400'">
                            <div class="size-5 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                                :class="symbol ? 'bg-emerald-50 border-emerald-100' : 'bg-zinc-50 border-zinc-100'">
                                <i data-lucide="check" class="size-3 transition-opacity"
                                    :class="symbol ? 'opacity-100' : 'opacity-0'"></i>
                            </div>
                            {{ __('Number or Symbol') }}
                        </li>
                    </ul>
                </div>
            </div>

            <div>
                <label for="password_confirmation"
                    class="block text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] mb-2 ml-1">{{ __('Confirm New Password') }}</label>
                <input type="password" wire:model="password_confirmation" id="password_confirmation" required
                    autocomplete="new-password" placeholder="••••••••"
                    class="block w-full px-4 py-3.5 bg-gray-50 border border-zinc-200 rounded-lg text-zinc-900 font-bold placeholder:text-zinc-300 focus:ring-4 focus:ring-indigo-brand/5 focus:border-indigo-brand focus:bg-white transition-all outline-none">
            </div>

            <div class="flex items-center gap-4 pt-8 border-t border-zinc-100">
                <button type="submit"
                    class="bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-4 px-8 rounded-[6px] shadow-xl shadow-indigo-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    {{ __('Update Password') }}
                </button>

                <x-action-message class="font-bold text-emerald-600 text-sm flex items-center gap-1.5"
                    on="password-updated">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    {{ __('Your password has been changed.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>