@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-2 text-left">
    <flux:heading size="xl" class="text-2xl">{{ $title }}</flux:heading>
    <flux:subheading class="leading-relaxed">{{ $description }}</flux:subheading>
</div>
