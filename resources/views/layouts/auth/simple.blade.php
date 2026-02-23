<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gray-100 antialiased">
        <div class="auth-shell relative flex min-h-svh items-start justify-center overflow-hidden p-4 sm:p-6 lg:items-center lg:p-8 [--color-accent:var(--color-teal-600)] [--color-accent-content:var(--color-teal-700)] [--color-accent-foreground:var(--color-white)]">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(13,148,136,0.16),transparent_42%),radial-gradient(circle_at_bottom_right,rgba(15,118,110,0.12),transparent_42%)]"></div>

            <div class="relative w-full max-w-5xl overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-2xl">
                <div class="grid lg:grid-cols-[1fr_1.1fr]">
                    <div class="hidden flex-col justify-between bg-gradient-to-br from-teal-600 to-teal-500 p-10 text-white lg:flex">
                        <div class="space-y-4">
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1 text-xs font-medium uppercase tracking-wide">
                                <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                                Chat system
                            </div>
                            <h1 class="text-3xl font-semibold leading-tight">Welcome to Frendie Chat</h1>
                            <p class="text-sm text-teal-50">
                                Sign in to continue conversations, invite teammates, and keep your space secure.
                            </p>
                        </div>

                        <ul class="space-y-3 text-sm text-teal-50">
                            <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-white"></span>Real-time messaging</li>
                            <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-white"></span>Simple account access</li>
                            <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-white"></span>Fast team collaboration</li>
                        </ul>
                    </div>

                    <div class="p-6 sm:p-8 lg:p-10">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-3 font-medium text-gray-700 transition hover:text-teal-700" wire:navigate>
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-600 text-white shadow-sm">
                                <x-app-logo-icon class="size-5 fill-current" />
                            </span>
                            <span class="text-sm tracking-tight">Frendie</span>
                        </a>

                        <div class="mt-6 flex flex-col gap-6">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @fluxScripts
    </body>
</html>
