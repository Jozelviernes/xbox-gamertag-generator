<section class="space-y-6">
    <header>
        <h2 class="text-lg font-bold text-red-600">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-[#64748B]">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="rounded-xl bg-red-600 px-5 py-3 text-sm font-extrabold uppercase tracking-[0.12em] text-white shadow-sm transition hover:bg-red-700 active:scale-[0.98]"
    >
        {{ __('Delete Account') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h2 class="text-xl font-bold text-[#050505]">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-sm leading-relaxed text-[#64748B]">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <label for="password" class="mb-2 block text-sm font-semibold text-[#050505]">
                    {{ __('Password') }}
                </label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="{{ __('Enter your password') }}"
                    class="w-full rounded-xl border border-[#E2E8F0] bg-white px-4 py-3 text-[#050505] shadow-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/15"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-sm text-red-500" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="rounded-xl border border-[#E2E8F0] bg-white px-4 py-2.5 text-sm font-semibold text-[#475569] transition hover:bg-[#F8FAFC]"
                >
                    {{ __('Cancel') }}
                </button>

                <button
                    type="submit"
                    class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-extrabold uppercase tracking-[0.12em] text-white transition hover:bg-red-700"
                >
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>