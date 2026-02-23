<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('home', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="min-h-screen bg-[#f0f2f5] p-3 sm:p-6">
    <div class="mx-auto w-full max-w-3xl overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-xl">
        <div class="flex items-center gap-3 bg-[#008069] px-4 py-4 text-white sm:px-6">
            <a
                href="{{ route('home') }}"
                class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/15 transition hover:bg-white/25"
                wire:navigate
                aria-label="Back to chat"
            >
                <span class="text-lg leading-none">&larr;</span>
            </a>
            <div>
                <h1 class="text-lg font-semibold">{{ __('Profile') }}</h1>
                <p class="text-xs text-white/80">{{ __('Manage your account details') }}</p>
            </div>
        </div>

        <div class="space-y-4 p-4 sm:p-6">
            <div class="rounded-2xl border border-gray-200 bg-[#f7fffa] p-5">
                <div class="flex flex-col items-center gap-3 text-center">
                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-[#00a884] text-4xl font-semibold text-white shadow-sm">
                        {{ strtoupper(substr($name ?: 'U', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-gray-900">{{ $name }}</p>
                        <p class="text-sm text-gray-500">{{ __('Tap fields below to edit your details') }}</p>
                    </div>
                </div>
            </div>

            <form wire:submit="updateProfileInformation" class="space-y-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-4">
                    <label for="profile-name" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#008069]">
                        {{ __('Name') }}
                    </label>
                    <input
                        id="profile-name"
                        wire:model="name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm text-gray-800 outline-none transition focus:border-[#00a884] focus:bg-white"
                    />
                    @error('name')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-4">
                    <label for="profile-email" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-[#008069]">
                        {{ __('Email') }}
                    </label>
                    <input
                        id="profile-email"
                        wire:model="email"
                        type="email"
                        required
                        autocomplete="email"
                        class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm text-gray-800 outline-none transition focus:border-[#00a884] focus:bg-white"
                    />
                    @error('email')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if ($this->hasUnverifiedEmail)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-sm text-amber-800">
                            {{ __('Your email is not verified yet.') }}
                        </p>
                        <button
                            type="button"
                            class="mt-2 text-sm font-semibold text-[#008069] underline underline-offset-4"
                            wire:click.prevent="resendVerificationNotification"
                        >
                            {{ __('Send verification email again') }}
                        </button>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-xs font-semibold text-emerald-700">
                                {{ __('A new verification link has been sent.') }}
                            </p>
                        @endif
                    </div>
                @endif

                <div class="flex items-center justify-between gap-3">
                    <x-action-message class="text-sm font-medium text-emerald-700" on="profile-updated">
                        {{ __('Saved successfully.') }}
                    </x-action-message>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-full bg-[#00a884] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#029c7b]"
                        data-test="update-profile-button"
                    >
                        {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
