<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-neutral-900 text-secondary border border-neutral-700 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2 focus:ring-offset-accent disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
