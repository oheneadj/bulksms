<section
    class="mt-12 p-8 bg-rose-50 rounded-xl border border-rose-100 flex flex-col md:flex-row md:items-center justify-between gap-6"
    x-data="{ showDeleteModal: false }">
    <div>
        <h3 class="text-lg font-bold text-red-900 tracking-tight">{{ __('Delete Account') }}</h3>
        <p class="text-sm text-red-700/70 font-medium mt-1">
            {{ __('Permanently remove your account and all associated data.') }}
        </p>
    </div>

    <button @click="showDeleteModal = true"
        class="bg-rose-500 hover:bg-rose-600 text-white font-bold px-8 py-3 rounded-[6px] shadow-lg shadow-rose-500/20 transition-all active:scale-[0.98]">
        {{ __('Delete Account') }}
    </button>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0"
        role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/20 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false">
        </div>

        <!-- Modal Panel -->
        <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-10">

            <form method="POST" wire:submit="deleteUser" class="space-y-6">
                <div>
                    <h2 class="text-2xl font-extrabold text-zinc-900 tracking-tight mb-4">
                        {{ __('Are you absolutely sure?') }}
                    </h2>

                    <p class="text-zinc-500 font-medium leading-relaxed">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently removed. This action cannot be undone. Please enter your password to confirm.') }}
                    </p>
                </div>

                <div>
                    <label for="password"
                        class="block text-[10px] font-extrabold text-zinc-400 uppercase tracking-[0.2em] mb-2 ml-1">{{ __('Confirm Password') }}</label>
                    <input type="password" wire:model="password" id="password" placeholder="••••••••"
                        class="block w-full px-4 py-3.5 bg-gray-50 border border-zinc-200 rounded-lg text-zinc-900 font-bold placeholder:text-zinc-300 focus:ring-4 focus:ring-rose-500/5 focus:border-rose-500 focus:bg-white transition-all outline-none">
                    @error('password') <p class="mt-2 text-xs text-rose-600 font-bold flex items-center gap-1.5 ml-1">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        {{ $message }}
                    </p> @enderror
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-zinc-100">
                    <button type="button" @click="showDeleteModal = false"
                        class="font-bold text-zinc-400 hover:text-zinc-900 px-6 py-3 transition-colors order-2 sm:order-1 text-sm">
                        {{ __('Keep Account') }}
                    </button>

                    <button type="submit"
                        class="bg-rose-500 hover:bg-rose-600 text-white font-bold px-8 py-3 rounded-[6px] shadow-lg shadow-rose-500/20 active:scale-[0.98] transition-all order-1 sm:order-2 text-sm">
                        {{ __('Delete Permanently') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>