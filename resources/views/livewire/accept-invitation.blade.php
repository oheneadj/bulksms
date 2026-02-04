<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-zinc-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center mb-6">
            <div
                class="w-16 h-16 rounded-2xl bg-indigo-brand flex items-center justify-center shadow-xl shadow-indigo-500/20">
                <x-app-logo-icon class="text-white w-10 h-10 fill-current" />
            </div>
        </div>
        <h2 class="text-center text-3xl font-extrabold text-zinc-900 tracking-tight">Join Your Team</h2>
        <p class="mt-2 text-center text-sm font-medium text-zinc-600">
            You've been invited to join <span class="text-indigo-600 font-bold">{{ $invitation->tenant->name }}</span>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-2xl shadow-zinc-200/50 sm:rounded-2xl sm:px-10 border border-zinc-100">
            <form wire:submit.prevent="accept" class="space-y-6">
                <!-- Name -->
                <div class="space-y-2">
                    <label for="name" class="block text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Full
                        Name</label>
                    <div class="relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400 group-focus-within:text-indigo-500 transition-colors">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <input id="name" type="text" wire:model="name" required
                            class="block w-full pl-10 pr-3 py-3 border border-zinc-200 rounded-xl leading-5 bg-zinc-50 placeholder-zinc-400 focus:outline-none focus:ring-0 focus:border-indigo-500 focus:bg-white transition-all font-medium text-sm"
                            placeholder="John Doe">
                    </div>
                    @error('name') <p class="mt-1 text-xs font-bold text-rose-600 uppercase tracking-wider">
                    {{ $message }}</p> @enderror
                </div>

                <!-- Email (Read-only) -->
                <div class="space-y-2 opacity-60">
                    <label class="block text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Email
                        Address</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" value="{{ $invitation->email }}" disabled
                            class="block w-full pl-10 pr-3 py-3 border border-zinc-200 rounded-xl leading-5 bg-zinc-100 font-medium text-sm">
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <label for="password"
                        class="block text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Create
                        Password</label>
                    <div class="relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400 group-focus-within:text-indigo-500 transition-colors">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input id="password" type="password" wire:model="password" required
                            class="block w-full pl-10 pr-3 py-3 border border-zinc-200 rounded-xl leading-5 bg-zinc-50 placeholder-zinc-400 focus:outline-none focus:ring-0 focus:border-indigo-500 focus:bg-white transition-all font-medium text-sm"
                            placeholder="••••••••">
                    </div>
                    @error('password') <p class="mt-1 text-xs font-bold text-rose-600 uppercase tracking-wider">
                    {{ $message }}</p> @enderror
                </div>

                <!-- Password Confirmation -->
                <div class="space-y-2">
                    <label for="password_confirmation"
                        class="block text-xs font-extrabold text-zinc-500 uppercase tracking-wider">Confirm
                        Password</label>
                    <div class="relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400 group-focus-within:text-indigo-500 transition-colors">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <input id="password_confirmation" type="password" wire:model="password_confirmation" required
                            class="block w-full pl-10 pr-3 py-3 border border-zinc-200 rounded-xl leading-5 bg-zinc-50 placeholder-zinc-400 focus:outline-none focus:ring-0 focus:border-indigo-500 focus:bg-white transition-all font-medium text-sm"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-lg shadow-indigo-500/20 text-sm font-extrabold text-white bg-indigo-brand hover:bg-indigo-brand-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all active:scale-[0.98]">
                        Complete Registration
                    </button>
                    <p
                        class="mt-4 text-[10px] text-center font-bold text-zinc-400 uppercase tracking-widest leading-relaxed">
                        By completing registration, you agree to the team's shared workspace and policies.
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => lucide.createIcons());
    </script>
</div>