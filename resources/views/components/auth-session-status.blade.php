@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'glass-banner border-emerald-200/80 bg-emerald-50/85 text-sm font-medium text-emerald-700 shadow-none dark:border-emerald-900/70 dark:bg-emerald-950/35 dark:text-emerald-200']) }}>
        {{ $status }}
    </div>
@endif
