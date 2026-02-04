<section class="w-full">
    @include('partials.settings-heading')

    <h1 class="sr-only">{{ __('Profile Settings') }}</h1>

    <x-settings.layout :heading="__('Profile Information')" :subheading="__('Update your name and account email address')">
        <form wire:submit="updateProfileInformation" class="space-y-6">
            <div>
                <label for="name"
                    class="block text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] mb-2 ml-1">{{ __('Display Name') }}</label>
                <input type="text" wire:model="name" id="name" required autofocus autocomplete="name"
                    class="block w-full px-4 py-3.5 bg-gray-50 border border-zinc-200 rounded-lg text-zinc-900 font-bold placeholder:text-zinc-300 focus:ring-4 focus:ring-indigo-brand/5 focus:border-indigo-brand focus:bg-white transition-all outline-none">
                @error('name') <p class="mt-1 text-xs text-red-500 font-medium ml-1 flex items-center gap-1">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    {{ $message }}
                </p> @enderror
            </div>

            <div>
                <label for="email"
                    class="block text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] mb-2 ml-1">{{ __('Email Address') }}</label>
                <input type="email" wire:model="email" id="email" required autocomplete="email"
                    class="block w-full px-4 py-3.5 bg-gray-50 border border-zinc-200 rounded-lg text-zinc-900 font-bold placeholder:text-zinc-300 focus:ring-4 focus:ring-indigo-brand/5 focus:border-indigo-brand focus:bg-white transition-all outline-none">
                @error('email') <p class="mt-1 text-xs text-red-500 font-medium ml-1 flex items-center gap-1">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    {{ $message }}
                </p> @enderror

                @if ($this->hasUnverifiedEmail)
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-5 mt-6">
                        <div class="flex items-center gap-3">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i>
                            <p class="text-amber-900 font-bold text-sm">
                                {{ __('Your email address is unverified.') }}
                            </p>
                        </div>

                        <button type="button"
                            class="text-xs font-bold text-amber-700 mt-3 flex items-center gap-1.5 hover:underline cursor-pointer bg-transparent border-0 p-0"
                            wire:click.prevent="resendVerificationNotification">
                            <i data-lucide="mail-plus" class="w-3.5 h-3.5"></i>
                            {{ __('Click here to re-send the verification email.') }}
                        </button>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-3 font-bold text-emerald-600 flex items-center gap-1.5 text-xs">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                {{ __('A new verification link has been sent.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4 pt-8 border-t border-zinc-100">
                <button type="submit"
                    class="bg-indigo-brand hover:bg-indigo-brand-hover text-white font-bold py-4 px-8 rounded-[6px] shadow-xl shadow-indigo-500/20 active:scale-[0.98] transition-all flex items-center gap-2">
                    {{ __('Save Changes') }}
                </button>

                <x-action-message class="font-bold text-emerald-600 text-sm flex items-center gap-1.5"
                    on="profile-updated">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    {{ __('Your profile has been updated.') }}
                </x-action-message>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <div class="mt-16 pt-12 border-t border-zinc-100">
                <livewire:settings.delete-user-form />
            </div>
        @endif
    </x-settings.layout>
</section>