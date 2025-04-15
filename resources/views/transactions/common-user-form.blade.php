<form method="POST" action="{{ route('transactions.store') }}">
    @csrf

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <x-select-input
        name="payee"
        label="Select a shopkeeper"
        :options="$shopkeeper->pluck('name_with_email', 'id')"
        required
    />

    {{-- Campo de valor mantido separado --}}
    <div class="mb-4">

            <x-input-label for="value" :value="__('Amount')" />
            <x-text-input id="value" step="0.01" name="value" type="number" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('insufficientBalance')" class="mt-2" />

            @error('value')
                <x-input-error :messages="$message" class="mt-2" />
            @enderror
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Transfer value') }}</x-primary-button>
        <input type="hidden" name="payer" value="{{ auth()->id() }}">

    </div>
</form>