@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-teal-100 bg-teal-50 px-4 py-3 text-sm font-medium text-teal-700']) }}>
        {{ $status }}
    </div>
@endif
