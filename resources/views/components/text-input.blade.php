@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-neutral-900 text-secondary placeholder-gray-400 border-neutral-800 focus:border-primary focus:ring-primary rounded-md shadow-sm']) }}>
