<section>
    <header>
        <h2 class="text-lg font-bold text-[#050505]">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-[#64748B]">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="mb-2 block text-sm font-semibold text-[#050505]">
                {{ __('Current Password') }}
            </label>
            <input
                id="update_password_current_password"
                name="current_password"
                type="password"
                autocomplete="current-password"
                class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
            />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-sm text-red-500" />
        </div>

        <div>
            <label for="update_password_password" class="mb-2 block text-sm font-semibold text-[#050505]">
                {{ __('New Password') }}
            </label>
            <input
                id="update_password_password"
                name="password"
                type="password"
                autocomplete="new-password"
                class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
            />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-sm text-red-500" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="mb-2 block text-sm font-semibold text-[#050505]">
                {{ __('Confirm Password') }}
            </label>
            <input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] shadow-sm outline-none transition focus:border-[#00D02B] focus:ring-2 focus:ring-[#00D02B]/15"
            />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-sm text-red-500" />
        </div>

        <div class="flex items-center gap-4">
            <button
                type="submit"
                class="rounded-xl bg-[#00D02B] px-5 py-3 text-sm font-extrabold uppercase tracking-[0.12em] text-black shadow-sm transition hover:bg-[#00e830] active:scale-[0.98]"
            >
                {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'password-updated')
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