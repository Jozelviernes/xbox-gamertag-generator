<section>
    <header>
        <h2 class="text-lg font-bold text-[#050505]">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-[#64748B]">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="mb-2 block text-sm font-semibold text-[#050505]">
                {{ __('Name') }}
            </label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name', $user->name) }}"
                required
                autofocus
                autocomplete="name"
                class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] placeholder:text-[#94A3B8] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
            />
            <x-input-error class="mt-2 text-sm text-red-500" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-[#050505]">
                {{ __('Email') }}
            </label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="username"
                class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] placeholder:text-[#94A3B8] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
            />
            <x-input-error class="mt-2 text-sm text-red-500" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                    <p class="text-sm text-amber-800">
                        {{ __('Your email address is unverified.') }}

                        <button
                            form="send-verification"
                            class="ml-1 font-semibold text-[#00B324] underline transition hover:text-[#00961e]"
                        >
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-700">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button
                type="submit"
                class="rounded-xl bg-[#00D02B] px-5 py-3 text-sm font-extrabold uppercase tracking-[0.12em] text-black shadow-sm transition hover:bg-[#00e830] active:scale-[0.98]"
            >
                {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-green-700"
                >
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>