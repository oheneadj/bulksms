<section class="w-full">
    @include('partials.settings-heading')

    <h1 class="sr-only">{{ __('Appearance Settings') }}</h1>

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <div x-data="{ appearance: $persist('system').as('appearance') }" class="flex gap-4">
            <template x-for="option in ['light', 'dark', 'system']">
                <label
                    class="cursor-pointer flex items-center gap-2 p-4 rounded-xl border border-zinc-200 transition-all hover:border-indigo-400 group"
                    :class="appearance === option ? 'bg-indigo-brand border-indigo-brand text-white shadow-lg shadow-indigo-500/20' : 'bg-white text-zinc-600 hover:bg-zinc-50'">
                    <input type="radio" name="appearance" :value="option" x-model="appearance" class="sr-only" @change="
                            if (option === 'dark') document.documentElement.classList.add('dark');
                            else if (option === 'light') document.documentElement.classList.remove('dark');
                            else if (window.matchMedia('(prefers-color-scheme: dark)').matches) document.documentElement.classList.add('dark');
                            else document.documentElement.classList.remove('dark');
                        ">
                    <span class="capitalize font-bold text-sm" x-text="option"></span>
                </label>
            </template>
        </div>
    </x-settings.layout>
</section>