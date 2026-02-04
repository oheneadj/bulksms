<section class="w-full">
    @include('partials.settings-heading')

    <h1 class="sr-only">{{ __('Two-Factor Authentication Settings') }}</h1>

    <x-settings.layout :heading="__('Two Factor Authentication')" :subheading="__('Manage your two-factor authentication settings')">
        <div class="flex flex-col w-full mx-auto space-y-6 text-sm" wire:cloak>
            @if ($twoFactorEnabled)
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold text-emerald-700 border border-emerald-100 uppercase tracking-widest">{{ __('Enabled') }}</span>
                    </div>

                    <p class="text-zinc-500">
                        {{ __('With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                    </p>

                    <livewire:settings.two-factor.recovery-codes :$requiresConfirmation />

                    <div class="flex justify-start">
                        <button wire:click="disable"
                            class="flex items-center gap-2 px-6 py-3 bg-rose-50 text-rose-700 border border-rose-100 rounded-[6px] font-bold text-sm hover:bg-rose-100 transition-colors shadow-sm">
                            <i data-lucide="shield-off" class="w-4 h-4"></i>
                            {{ __('Disable 2FA') }}
                        </button>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-1 text-[10px] font-extrabold text-zinc-500 border border-zinc-200 uppercase tracking-widest">{{ __('Disabled') }}</span>
                    </div>

                    <p class="text-zinc-500 text-sm">
                        {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                    </p>

                    <button wire:click="enable"
                        class="flex items-center gap-2 px-6 py-3 bg-indigo-brand text-white border border-indigo-brand rounded-[6px] font-bold text-sm hover:bg-indigo-brand-hover transition-colors shadow-lg shadow-indigo-500/20">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        {{ __('Enable 2FA') }}
                    </button>
                </div>
            @endif
        </div>
    </x-settings.layout>

    <!-- Modal -->
    <div x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/20 backdrop-blur-sm transition-opacity"
            @click="show = false; $wire.closeModal()"></div>

        <!-- Panel -->
        <div x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-10">
            <div class="space-y-6">
                <div class="flex flex-col items-center space-y-4">
                    <div class="p-0.5 w-auto rounded-full border border-zinc-100 bg-white shadow-sm">
                        <div class="p-2.5 rounded-full border border-zinc-200 overflow-hidden bg-zinc-50 relative">
                            <i data-lucide="qr-code" class="size-6 text-indigo-brand"></i>
                        </div>
                    </div>

                    <div class="space-y-2 text-center">
                        <h2 class="text-xl font-extrabold text-zinc-900 tracking-tight">
                            {{ $this->modalConfig['title'] }}
                        </h2>
                        <p class="text-sm text-zinc-500 font-medium">{{ $this->modalConfig['description'] }}</p>
                    </div>
                </div>

                @if ($showVerificationStep)
                    <div class="space-y-6">
                        <div class="flex flex-col items-center space-y-3 justify-center">
                            <div class="w-full">
                                <label for="code"
                                    class="text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] mb-2 ml-1 block text-center">OTP
                                    Code</label>
                                <input type="text" name="code" wire:model="code" id="code"
                                    class="block w-full text-center tracking-[0.5em] text-2xl font-bold bg-gray-50 border border-zinc-200 rounded-lg focus:ring-4 focus:ring-indigo-brand/5 focus:border-indigo-brand focus:bg-white transition-all outline-none py-4"
                                    placeholder="XXXXXX" maxlength="6" autofocus />
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <button wire:click="resetVerification"
                                class="flex-1 px-4 py-3 border border-zinc-200 text-zinc-400 hover:text-zinc-900 bg-white hover:bg-zinc-50 rounded-[6px] font-bold text-sm transition-colors">
                                {{ __('Back') }}
                            </button>

                            <button wire:click="confirmTwoFactor"
                                class="flex-1 px-4 py-3 bg-indigo-brand text-white rounded-[6px] font-bold text-sm hover:bg-indigo-brand-hover transition-colors shadow-lg shadow-indigo-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
                                x-bind:disabled="$wire.code.length < 6">
                                {{ __('Confirm') }}
                            </button>
                        </div>
                    </div>
                @else
                    @error('setupData')
                        <div class="rounded-xl bg-rose-50 p-6 border border-rose-100">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">{{ $message }}</h3>
                                </div>
                            </div>
                        </div>
                    @enderror

                    <div class="flex justify-center">
                        <div
                            class="relative w-64 overflow-hidden border border-zinc-200 rounded-xl aspect-square p-2 bg-white shadow-sm">
                            @empty($qrCodeSvg)
                                <div class="absolute inset-0 flex items-center justify-center bg-zinc-50/50 animate-pulse">
                                    <i data-lucide="loader-2" class="animate-spin h-8 w-8 text-indigo-brand"></i>
                                </div>
                            @else
                                <div class="flex items-center justify-center h-full">
                                    {!! $qrCodeSvg !!}
                                </div>
                            @endempty
                        </div>
                    </div>

                    <div>
                        <button wire:click="showVerificationIfNecessary"
                            class="w-full px-4 py-4 bg-indigo-brand text-white rounded-[6px] font-bold text-sm hover:bg-indigo-brand-hover transition-colors shadow-lg shadow-indigo-500/20 disabled:opacity-50"
                            :disabled="$errors->has('setupData')">
                            {{ $this->modalConfig['buttonText'] }}
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="relative flex items-center justify-center w-full">
                            <div class="absolute inset-0 w-full h-px top-1/2 bg-zinc-100"></div>
                            <span
                                class="relative px-3 text-[10px] font-extrabold bg-white text-zinc-400 uppercase tracking-widest">
                                {{ __('or, enter manually') }}
                            </span>
                        </div>

                        <div class="flex items-center space-x-2"
                            x-data="{copied: false, async copy() { try { await navigator.clipboard.writeText('{{ $manualSetupKey }}'); this.copied = true; setTimeout(() => this.copied = false, 1500); } catch (e) { console.warn('Could not copy'); } }}">
                            <div
                                class="flex items-stretch w-full border border-zinc-200 rounded-lg overflow-hidden bg-gray-50 group-focus-within:border-indigo-400 transition-colors">
                                @empty($manualSetupKey)
                                    <div class="flex items-center justify-center w-full p-4">
                                        <i data-lucide="loader-2" class="animate-spin h-4 w-4 text-indigo-brand"></i>
                                    </div>
                                @else
                                    <input type="text" readonly value="{{ $manualSetupKey }}"
                                        class="w-full px-4 py-3.5 bg-transparent outline-none text-xs font-bold text-zinc-900 border-none focus:ring-0" />
                                    <button @click="copy()"
                                        class="px-5 transition-colors border-l cursor-pointer border-zinc-200 bg-white hover:bg-gray-50 flex items-center justify-center text-zinc-400 hover:text-indigo-brand">
                                        <i x-show="!copied" data-lucide="copy" class="size-4"></i>
                                        <i x-show="copied" data-lucide="check" class="size-4 text-emerald-500"></i>
                                    </button>
                                @endempty
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>