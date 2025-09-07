@props(['name', 'options', 'selected' => null])

<div x-data="{
    open: false,
    value: '{{ $selected }}',
    label: '{{ $options[$selected] ?? array_values($options)[0] }}',
    options: {{ json_encode($options) }},
    selectOption(val, lbl) {
        this.value = val;
        this.label = lbl;
        this.open = false;
        this.$refs.hiddenInput.value = val;
        this.$refs.hiddenInput.form.submit();
    }
}" class="relative w-48">

    <input type="hidden" name="{{ $name }}" x-ref="hiddenInput" :value="value">

    <button @click="open = !open" type="button" class="relative w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        <span class="block truncate text-white" x-text="label"></span>
        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </span>
    </button>

    <div x-show="open" @click.away="open = false"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute mt-1 w-full rounded-md bg-gray-700 shadow-lg z-10" style="display: none;">
        <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
            <template x-for="(lbl, val) in options" :key="val">
                <li @click="selectOption(val, lbl)"
                    class="text-gray-300 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-600">
                    <span class="block truncate" :class="{ 'font-semibold text-white': value == val, 'font-normal': value != val }" x-text="lbl"></span>
                    <template x-if="value == val">
                        <span class="text-indigo-400 absolute inset-y-0 right-0 flex items-center pr-4">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </template>
                </li>
            </template>
        </ul>
    </div>
</div>
