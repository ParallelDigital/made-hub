@props(['id', 'name', 'type' => 'text', 'label', 'placeholder' => '', 'required' => false, 'value' => '', 'helper' => ''])

<div class="relative">
    <input id="{{ $id }}" 
           name="{{ $name }}" 
           type="{{ $type }}" 
           value="{{ $value }}"
           {{ $required ? 'required' : '' }}
           class="peer w-full px-4 py-3 rounded-lg border-2 border-gray-200 placeholder-transparent focus:border-primary transition-colors"
           placeholder="{{ $placeholder }}" />
    
    <label for="{{ $id }}" 
           class="absolute left-2 -top-2.5 text-sm text-gray-600 transition-all
                  peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-3.5 peer-placeholder-shown:left-4
                  peer-focus:-top-2.5 peer-focus:left-2 peer-focus:text-sm peer-focus:text-primary floating-label">
        {{ $label }}
    </label>
    
    @if($helper)
        <div class="mt-1 text-sm text-gray-500">{{ $helper }}</div>
    @endif
</div>
