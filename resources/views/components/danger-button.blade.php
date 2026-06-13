<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-danger text-xs uppercase tracking-[0.24em]']) }}>
    {{ $slot }}
</button>
