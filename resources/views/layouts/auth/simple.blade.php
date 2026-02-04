<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">

<head>
    @include('partials.head')
    <style>
        [x-cloak] {
            display: none !important;
        }

        .air-fade-in {
            animation: airFadeIn 0.4s ease-out;
        }

        @keyframes airFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div
        class="min-h-screen w-full bg-[#E6F0FF] flex items-center justify-center p-6 md:p-12 relative overflow-hidden font-sans">
        <!-- Floating Decorative Background Elements -->
        <div
            class="absolute top-[-10%] left-[-5%] w-[40%] h-[40%] bg-blue-100/40 rounded-full blur-[120px] pointer-events-none">
        </div>
        <div
            class="absolute bottom-[-10%] right-[-5%] w-[40%] h-[40%] bg-indigo-100/40 rounded-full blur-[120px] pointer-events-none">
        </div>

        <div class="w-full max-w-[1000px] relative z-10 air-fade-in">
            {{ $slot }}
        </div>

        <!-- App Logo Bottom Left -->
        <div class="absolute bottom-8 left-8 flex items-center gap-2 opacity-40">
            <x-app-logo-icon class="text-zinc-600 w-5 h-5 fill-current" />
            <span class="text-sm font-bold text-zinc-900 tracking-tight">{{ config('app.name') }}</span>
        </div>
    </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if (session('status'))
                const toast = new ToastMagic();
                let message = "{{ session('status') }}";
                if (message === 'verification-link-sent') {
                    message = "{{ __('A new verification link has been sent to your email address.') }}";
                }
                toast.success(message);
            @endif
        });
    </script>
    {!! ToastMagic::scripts() !!}
</body>

</html>