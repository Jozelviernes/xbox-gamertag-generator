<nav x-data="{ open: false }" class="sticky top-0 z-50 border-b border-[#E2E8F0] bg-white shadow-sm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">

            <!-- Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <svg width="32" height="32" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="1" y="1" width="38" height="38" rx="6" stroke="#00D02B" stroke-width="1.5"/>
                    <path d="M10 10 L20 20 L10 30" stroke="#00D02B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M20 10 L30 20 L20 30" stroke="#00D02B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.45"/>
                    <rect x="18" y="18" width="4" height="4" rx="1" fill="#00D02B"/>
                </svg>

                <div class="flex flex-col leading-none gap-0.5">
                    <span class="text-sm font-black uppercase tracking-widest text-[#050505]" style="font-family:'Orbitron',sans-serif;">
                        XBOX
                    </span>
                    <span class="text-[9px] font-bold uppercase tracking-[2px] text-[#00D02B]" style="font-family:'Orbitron',sans-serif;">
                        GAMERTAG GEN
                    </span>
                </div>
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden items-center gap-1 md:flex">
                <a
                    href="{{ route('dashboard') }}"
                    class="rounded-lg px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-[#F8FAFC] text-[#050505]' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#050505]' }}"
                >
                    Dashboard
                </a>

                <a
                    href="{{ route('profile.edit') }}"
                    class="rounded-lg px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('profile.*') ? 'bg-[#F8FAFC] text-[#050505]' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#050505]' }}"
                >
                    Profile
                </a>

                <div class="ml-3 flex items-center gap-3 border-l border-[#E2E8F0] pl-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-xs font-semibold text-[#050505]">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] text-[#64748B]">{{ Auth::user()->email }}</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="rounded-lg bg-[#00D02B] px-4 py-2 text-sm font-bold uppercase tracking-[0.1em] text-black transition hover:bg-[#00e830] active:scale-[0.98]"
                        >
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            <!-- Mobile Hamburger -->
            <button
                @click="open = ! open"
                type="button"
                aria-label="Toggle menu"
                :aria-expanded="open.toString()"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#E2E8F0] bg-white text-[#475569] transition hover:border-[#00D02B]/30 hover:text-[#00D02B] md:hidden"
            >
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div x-show="open" x-transition class="border-t border-[#E2E8F0] bg-white md:hidden">
        <div class="mx-auto max-w-7xl space-y-1 px-4 py-3 sm:px-6">
            <a
                href="{{ route('dashboard') }}"
                class="block rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-[#F8FAFC] text-[#050505]' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#050505]' }}"
            >
                Dashboard
            </a>

            <a
                href="{{ route('profile.edit') }}"
                class="block rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('profile.*') ? 'bg-[#F8FAFC] text-[#050505]' : 'text-[#475569] hover:bg-[#F8FAFC] hover:text-[#050505]' }}"
            >
                Profile
            </a>

            <div class="border-t border-[#E2E8F0] px-4 pt-4 pb-2">
                <p class="text-sm font-semibold text-[#050505]">{{ Auth::user()->name }}</p>
                <p class="mt-1 text-xs text-[#64748B]">{{ Auth::user()->email }}</p>
            </div>

            <div class="px-4 pb-3">
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
</nav>