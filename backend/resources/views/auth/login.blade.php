<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-[#050505] mb-2">
                Email Address
            </label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="Enter your email"
                class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] placeholder:text-[#94A3B8] focus:border-[#00D02B] focus:ring-0 outline-none transition"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500" />
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-[#050505] mb-2">
                Password
            </label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Enter your password"
                class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] placeholder:text-[#94A3B8] focus:border-[#00D02B] focus:ring-0 outline-none transition"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-[#64748B]">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="rounded border-[#E2E8F0] bg-white text-[#00D02B] focus:ring-[#00D02B]"
                >
                <span>Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-sm text-[#00D02B] hover:text-[#00a021] transition">
                    Forgot password?
                </a>
            @endif
        </div>

        <button
            type="submit"
            class="w-full rounded-xl bg-[#00D02B] px-4 py-3 text-sm font-extrabold uppercase tracking-[0.15em] text-black transition hover:bg-[#00e830] active:scale-[0.98]"
        >
            Log In
        </button>
    </form>
</x-guest-layout>