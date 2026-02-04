@props([
    'sidebar' => false,
])


       <a {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <div class="flex aspect-square w-8 h-8 items-center justify-center rounded-md bg-zinc-900 text-white dark:bg-white dark:text-black">
        <x-app-logo-icon class="w-5 h-5 fill-current" />
    </div>
    <span class="text-lg font-bold tracking-tight text-zinc-900 dark:text-white">
        {{ config('app.name', 'Laravel') }}
    </span>
</a>
