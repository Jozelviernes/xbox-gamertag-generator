<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>

<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" />
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" />
<link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png" />
<link rel="icon" type="image/png" sizes="512x512" href="/android-chrome-512x512.png" />
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
<link rel="manifest" href="/site.webmanifest" />
<meta name="theme-color" content="#2f8612" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Orbitron:wght@700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F8FAFC] text-[#050505]" style="font-family: 'Inter', sans-serif;">
<div class="min-h-screen">

    <!-- ── Header ── -->
    <header class="sticky top-0 z-50 border-b border-[#E2E8F0] bg-white shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">

            <!-- Logo — matches Astro header -->
            <a href="{{ route('admin.glossaries.index') }}" class="flex items-center gap-3">
                <svg width="32" height="32" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="1" y="1" width="38" height="38" rx="6" stroke="#00D02B" stroke-width="1.5"/>
                    <path d="M10 10 L20 20 L10 30" stroke="#00D02B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M20 10 L30 20 L20 30" stroke="#00D02B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.45"/>
                    <rect x="18" y="18" width="4" height="4" rx="1" fill="#00D02B"/>
                </svg>
                <div class="flex flex-col leading-none gap-0.5">
                    <span class="text-sm font-black text-[#050505] tracking-widest uppercase" style="font-family:'Orbitron',sans-serif;">XBOX</span>
                    <span class="text-[9px] font-bold text-[#00D02B] tracking-[2px] uppercase" style="font-family:'Orbitron',sans-serif;">GAMERTAG GEN</span>
                </div>
            </a>

            <!-- Desktop nav -->
            <div class="hidden items-center gap-1 md:flex">
                <a
                    href="{{ route('admin.glossaries.index') }}"
                    class="rounded-lg px-3 py-2 text-sm font-semibold text-[#475569] transition hover:bg-[#F8FAFC] hover:text-[#050505] {{ request()->routeIs('admin.glossaries.*') ? 'bg-[#F8FAFC] text-[#050505]' : '' }}"
                >
                    Glossary
                </a>
                <a
                    href="{{ route('profile.edit') }}"
                    class="rounded-lg px-3 py-2 text-sm font-semibold text-[#475569] transition hover:bg-[#F8FAFC] hover:text-[#050505] {{ request()->routeIs('profile.*') ? 'bg-[#F8FAFC] text-[#050505]' : '' }}"
                >
                    Profile
                </a>
                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button
                        type="submit"
                        class="rounded-lg bg-[#00D02B] px-4 py-2 text-sm font-bold uppercase tracking-[0.1em] text-black transition hover:bg-[#00e830] active:scale-[0.98]"
                    >
                        Logout
                    </button>
                </form>
            </div>

            <!-- Mobile hamburger -->
            <button
                id="admin-menu-btn"
                type="button"
                aria-label="Toggle menu"
                aria-expanded="false"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#E2E8F0] bg-white text-[#475569] transition hover:border-[#00D02B]/30 hover:text-[#00D02B] md:hidden"
            >
                <svg id="admin-icon-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg id="admin-icon-close" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile menu -->
        <div id="admin-mobile-menu" class="hidden border-t border-[#E2E8F0] bg-white md:hidden">
            <div class="mx-auto max-w-7xl space-y-1 px-4 py-3 sm:px-6">
                <a
                    href="{{ route('admin.glossaries.index') }}"
                    class="block rounded-xl px-4 py-3 text-sm font-semibold text-[#475569] transition hover:bg-[#F8FAFC] hover:text-[#050505] {{ request()->routeIs('admin.glossaries.*') ? 'bg-[#F8FAFC] text-[#050505]' : '' }}"
                >
                    Glossary
                </a>
                <a
                    href="{{ route('profile.edit') }}"
                    class="block rounded-xl px-4 py-3 text-sm font-semibold text-[#475569] transition hover:bg-[#F8FAFC] hover:text-[#050505] {{ request()->routeIs('profile.*') ? 'bg-[#F8FAFC] text-[#050505]' : '' }}"
                >
                    Profile
                </a>
                <div class="pt-1 pb-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full rounded-xl bg-[#00D02B] px-4 py-3 text-sm font-bold uppercase tracking-[0.1em] text-black transition hover:bg-[#00e830]"
                        >
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- ── Page content ── -->
    <main>
        @yield('content')
    </main>

</div>

<script>
    const btn       = document.getElementById('admin-menu-btn');
    const menu      = document.getElementById('admin-mobile-menu');
    const iconOpen  = document.getElementById('admin-icon-open');
    const iconClose = document.getElementById('admin-icon-close');

    btn?.addEventListener('click', () => {
        const isHidden = menu.classList.contains('hidden');
        menu.classList.toggle('hidden', !isHidden);
        iconOpen.classList.toggle('hidden', isHidden);
        iconClose.classList.toggle('hidden', !isHidden);
        btn.setAttribute('aria-expanded', String(isHidden));
    });
</script>
</body>
</html>