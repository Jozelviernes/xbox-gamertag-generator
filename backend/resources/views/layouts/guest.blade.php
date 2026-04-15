<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Xbox Gamertag Generator Admin') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Orbitron:wght@700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F8FAFC] text-[#050505] antialiased" style="font-family: 'Inter', sans-serif;">
    <div class="relative flex min-h-screen items-center justify-center px-4 py-10">
        <div
            class="pointer-events-none absolute inset-0"
            style="background: radial-gradient(ellipse 70% 55% at 50% 0%, rgba(0,208,43,0.08) 0%, transparent 65%);">
        </div>

        <div class="relative z-10 w-full max-w-md">
            <div class="mb-6 text-center">
                <a href="/" class="inline-flex items-center justify-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-[#00D02B]/25 bg-white shadow-sm">
                        <span class="text-xl font-black text-[#00D02B]" style="font-family: 'Orbitron', sans-serif;">XG</span>
                    </div>
                </a>

                <p class="mt-4 text-[11px] font-bold uppercase tracking-[0.22em] text-[#00D02B]">
                    Admin Access
                </p>
                <h1 class="mt-2 text-3xl font-black text-[#050505]" style="font-family: 'Orbitron', sans-serif;">
                    Xbox Gamertag Admin
                </h1>
                <p class="mt-2 text-sm text-[#64748B]">
                    Sign in to manage glossary content and admin tools.
                </p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-xl">
                <div class="h-1 w-full bg-[#00D02B]"></div>

                <div class="p-6 sm:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>