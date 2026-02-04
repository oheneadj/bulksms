@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <h1 class="text-2xl font-extrabold text-zinc-900 tracking-tight">{{ $title }}</h1>
    <p class="text-zinc-500 font-medium">{{ $description }}</p>
</div>
