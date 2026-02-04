<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-white min-h-screen flex flex-col md:flex-row antialiased font-sans">
    {{ $slot }}
    <script>
        lucide.createIcons();
    </script>
</body>

</html>