{{-- resources/views/components/select-input.blade.php --}}
@props([
    'name',
    'label',
    'options',
    'selected' => old($name),
    'required' => false,
    'disabled' => false,
])

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="block text-gray-700 text-sm font-bold mb-2">
            {{ $label }}:
        </label>
    @endif

    <select 
        name="{{ $name }}" 
        id="{{ $name }}" 
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline']) }}
    >
        @if($slot->isNotEmpty())
            {{ $slot }}
        @else
            <option value="">Selecione...</option>
            @foreach($options as $key => $value)
                <option value="{{ $key }}" {{ $selected == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        @endif
    </select>
</div>