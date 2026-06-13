<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-secondary text-xs uppercase tracking-[0.24em] disabled:opacity-50']) }}>
    {{ $slot }}
</button>
