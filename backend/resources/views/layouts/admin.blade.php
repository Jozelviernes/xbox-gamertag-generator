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

    <style>
        /* ── White theme tokens (mirrors global.css white section vars) ── */
        :root {
            --white-bg:        #FFFFFF;
            --white-bg-subtle: hsl(220 14% 96%);
            --white-heading:   hsl(218 30% 9%);
            --white-body:      hsl(215 16% 47%);
            --white-border:    hsl(220 13% 91%);
            --brand:           hsl(107 76% 30%);
            --brand-light:     hsl(107 76% 38%);
            --brand-glow:      hsl(107 76% 26% / 0.10);
            --radius:          0.75rem;
            --transition:      all 0.2s ease;
            --shadow-sm:       0 1px 3px hsl(218 30% 9% / 0.08),
                               0 1px 2px hsl(218 30% 9% / 0.04);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html {
            color-scheme: light;
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background-color: var(--white-bg-subtle);
            color: var(--white-heading);
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            font-size: 14px;
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        /* ── HEADER ── */
        .admin-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background-color: var(--white-bg);
            border-bottom: 1px solid var(--white-border);
            box-shadow: var(--shadow-sm);
        }

        .admin-header-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        /* ── LOGO ── */
        .admin-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
        }

        .admin-logo img {
            height: 34px;
            width: auto;
            display: block;
        }

        /* ── DESKTOP NAV ── */
        .admin-nav {
            display: none;
            align-items: center;
            gap: 2px;
        }

        @media (min-width: 768px) {
            .admin-nav { display: flex; }
        }

        /* Mirrors your .nav-link style from global.css */
        .admin-nav-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            height: 40px;
            padding: 0 14px;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--white-body);
            text-decoration: none;
            border-radius: var(--radius);
            transition: color 0.2s ease;
        }

        .admin-nav-link:hover {
            color: var(--white-heading);
        }

        /* Green underline — mirrors .nav-link::after from global.css */
        .admin-nav-link::after {
            content: '';
            position: absolute;
            left: 14px;
            right: 14px;
            bottom: 6px;
            height: 2px;
            border-radius: 9999px;
            background: var(--brand);
            transform: scaleX(0);
            transform-origin: center;
            transition: transform 0.3s ease;
        }

        .admin-nav-link:hover::after,
        .admin-nav-link.is-active::after {
            transform: scaleX(1);
        }

        .admin-nav-link.is-active {
            color: var(--white-heading);
        }

        /* ── LOGOUT BUTTON — mirrors .btn-primary ── */
        .btn-logout {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 36px;
            padding: 0 18px;
            margin-left: 10px;
            background: var(--brand);
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }

        .btn-logout:hover  { background: var(--brand-light); }
        .btn-logout:active { transform: scale(0.98); }

        /* ── HAMBURGER ── */
        .admin-hamburger {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--white-bg);
            border: 1px solid var(--white-border);
            border-radius: var(--radius);
            color: var(--white-body);
            cursor: pointer;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .admin-hamburger:hover {
            border-color: var(--brand);
            color: var(--brand);
            background: var(--brand-glow);
        }

        @media (min-width: 768px) {
            .admin-hamburger { display: none; }
        }

        /* ── MOBILE MENU ── */
        .admin-mobile-menu {
            display: none;
            border-top: 1px solid var(--white-border);
            background: var(--white-bg);
        }

        .admin-mobile-menu.is-open {
            display: block;
        }

        .admin-mobile-menu-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0.75rem 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .admin-mobile-link {
            display: flex;
            align-items: center;
            padding: 12px 14px;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--white-body);
            text-decoration: none;
            border-radius: var(--radius);
            border: 1px solid transparent;
            transition: var(--transition);
        }

        .admin-mobile-link:hover,
        .admin-mobile-link.is-active {
            color: var(--white-heading);
            background: var(--white-bg-subtle);
            border-color: var(--white-border);
        }

        .btn-logout-mobile {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 44px;
            margin-top: 6px;
            background: var(--brand);
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-logout-mobile:hover  { background: var(--brand-light); }
        .btn-logout-mobile:active { transform: scale(0.98); }

        /* ── PAGE WRAPPER ── */
        .admin-page {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        @media (max-width: 640px) {
            .admin-header-inner      { padding: 0 1rem; }
            .admin-mobile-menu-inner { padding: 0.75rem 1rem 1rem; }
            .admin-page              { padding: 1.25rem 1rem; }
        }
    </style>
</head>
<body>

    <!-- ── Header ── -->
    <header class="admin-header">
        <div class="admin-header-inner">

            <!-- Logo -->
            <a href="{{ route('admin.glossaries.index') }}" class="admin-logo">
                <img
                    src="{{ asset('logo.webp') }}"
                    alt="Xbox Gamertag Generator logo"
                    width="40"
                    height="40"
                    style="width:40px;height:40px;object-fit:contain;flex-shrink:0;"
                />
                <div style="min-width:0;line-height:1;">
                    <span style="display:block;font-family:'Orbitron',sans-serif;font-size:0.875rem;font-weight:900;text-transform:uppercase;letter-spacing:0.18em;color:var(--white-heading);">
                        Xbox
                    </span>
                    <span style="display:block;font-family:'Inter',sans-serif;font-size:0.62rem;font-weight:700;text-transform:uppercase;letter-spacing:0.22em;color:var(--brand);">
                        Gamertag Generator
                    </span>
                </div>
            </a>

            <!-- Desktop nav -->
            <nav class="admin-nav" aria-label="Admin navigation">
                <a
                    href="{{ route('admin.glossaries.index') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.glossaries.*') ? 'is-active' : '' }}"
                >
                    Glossary
                </a>
                <a
                    href="{{ route('profile.edit') }}"
                    class="admin-nav-link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}"
                >
                    Profile
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn-logout">Logout</button>
                </form>
            </nav>

            <!-- Mobile hamburger -->
            <button
                id="admin-menu-btn"
                type="button"
                class="admin-hamburger"
                aria-label="Toggle menu"
                aria-expanded="false"
                aria-controls="admin-mobile-menu"
            >
                <svg id="admin-icon-open" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg id="admin-icon-close" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile menu -->
        <div id="admin-mobile-menu" class="admin-mobile-menu" role="navigation" aria-label="Mobile navigation">
            <div class="admin-mobile-menu-inner">
                <a
                    href="{{ route('admin.glossaries.index') }}"
                    class="admin-mobile-link {{ request()->routeIs('admin.glossaries.*') ? 'is-active' : '' }}"
                >
                    Glossary
                </a>
                <a
                    href="{{ route('profile.edit') }}"
                    class="admin-mobile-link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}"
                >
                    Profile
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn-logout-mobile">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <!-- ── Page content ── -->
    <main class="admin-page">
        @yield('content')
    </main>

<script>
    const btn       = document.getElementById('admin-menu-btn');
    const menu      = document.getElementById('admin-mobile-menu');
    const iconOpen  = document.getElementById('admin-icon-open');
    const iconClose = document.getElementById('admin-icon-close');

    btn?.addEventListener('click', () => {
        const isOpen = menu.classList.contains('is-open');
        menu.classList.toggle('is-open', !isOpen);
        iconOpen.style.display  = isOpen ? 'block' : 'none';
        iconClose.style.display = isOpen ? 'none'  : 'block';
        btn.setAttribute('aria-expanded', String(!isOpen));
    });
</script>
</body>
</html>