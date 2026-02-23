@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Frendie" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md border border-zinc-200 bg-white">
            <x-app-logo-icon class="size-full object-cover" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Frendie" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md border border-zinc-200 bg-white">
            <x-app-logo-icon class="size-full object-cover" />
        </x-slot>
    </flux:brand>
@endif
