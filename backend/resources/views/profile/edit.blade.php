<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-[#00D02B]">
                Account Settings
            </p>
            <h2 class="text-2xl font-black text-[#050505]" style="font-family: 'Orbitron', sans-serif;">
                Profile
            </h2>
            <p class="text-sm text-[#64748B]">
                Manage your account information, password, and account settings.
            </p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-[#F8FAFC] py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
                <div class="border-b border-[#E2E8F0] px-6 py-4">
                    <h3 class="text-base font-bold text-[#050505]">Profile Information</h3>
                    <p class="mt-1 text-sm text-[#64748B]">
                        Update your account’s profile information and email address.
                    </p>
                </div>

                <div class="px-6 py-6">
                    <div class="max-w-2xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
                <div class="border-b border-[#E2E8F0] px-6 py-4">
                    <h3 class="text-base font-bold text-[#050505]">Update Password</h3>
                    <p class="mt-1 text-sm text-[#64748B]">
                        Use a strong password to keep your account secure.
                    </p>
                </div>

                <div class="px-6 py-6">
                    <div class="max-w-2xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-red-200 bg-white shadow-sm">
                <div class="border-b border-red-100 px-6 py-4">
                    <h3 class="text-base font-bold text-red-600">Delete Account</h3>
                    <p class="mt-1 text-sm text-[#64748B]">
                        Permanently delete your account and all of its data.
                    </p>
                </div>

                <div class="px-6 py-6">
                    <div class="max-w-2xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>