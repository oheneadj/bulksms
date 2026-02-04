<div class="py-6 space-y-6 border shadow-sm rounded-xl border-zinc-200 dark:border-white/10" wire:cloak
    x-data="{ showRecoveryCodes: false }">
    <div class="px-6 space-y-2">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            <h3 class="text-lg font-bold text-zinc-900 tracking-tight">{{ __('2FA Recovery Codes') }}</h3>
        </div>
        <p class="text-sm text-zinc-500 font-medium">
            {{ __('Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.') }}
        </p>
    </div>

    <div class="px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <button x-show="!showRecoveryCodes" @click="showRecoveryCodes = true;" aria-expanded="false"
                aria-controls="recovery-codes-section"
                class="flex items-center gap-2 px-6 py-3 bg-white border border-zinc-200 rounded-[6px] font-bold text-sm hover:bg-zinc-50 transition-colors shadow-sm text-zinc-600">
                <i data-lucide="eye" class="size-4"></i>
                {{ __('View Recovery Codes') }}
            </button>

            <button x-show="showRecoveryCodes" @click="showRecoveryCodes = false" aria-expanded="true"
                aria-controls="recovery-codes-section"
                class="flex items-center gap-2 px-6 py-3 bg-white border border-zinc-200 rounded-[6px] font-bold text-sm hover:bg-zinc-50 transition-colors shadow-sm text-zinc-600">
                <i data-lucide="eye-off" class="size-4"></i>
                {{ __('Hide Recovery Codes') }}
            </button>

            @if (filled($recoveryCodes))
                <button x-show="showRecoveryCodes" wire:click="regenerateRecoveryCodes"
                    class="flex items-center gap-2 px-6 py-3 bg-white border border-zinc-200 rounded-[6px] font-bold text-sm hover:bg-zinc-50 transition-colors shadow-sm text-zinc-600">
                    <i data-lucide="refresh-cw" class="size-4"></i>
                    {{ __('Regenerate Codes') }}
                </button>
            @endif
        </div>

        <div x-show="showRecoveryCodes" x-transition id="recovery-codes-section" class="relative overflow-hidden"
            x-bind:aria-hidden="!showRecoveryCodes">
            <div class="mt-3 space-y-3">
                @error('recoveryCodes')
                    <div class="rounded-xl bg-red-50 p-6 border border-red-100">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true">
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

                @if (filled($recoveryCodes))
                    <div class="grid gap-1 p-4 font-mono text-sm rounded-lg bg-zinc-100 dark:bg-white/5" role="list"
                        aria-label="{{ __('Recovery codes') }}">
                        @foreach($recoveryCodes as $code)
                            <div role="listitem" class="select-text" wire:loading.class="opacity-50 animate-pulse">
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-zinc-500">
                        {{ __('Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate Codes above.') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>