<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-primary text-accent border border-primary/30 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-primary/90 focus:bg-primary/90 active:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2 focus:ring-offset-accent transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
