{{-- Session flash messages: ->with('success', …) / ->with('error', …). --}}
@if (session('success') || session('error') || $errors->has('status'))
    <div class="mb-6 space-y-3">
        @if (session('success'))
            <x-alert type="success" dismissible>{{ session('success') }}</x-alert>
        @endif

        @if (session('error'))
            <x-alert type="error" dismissible>{{ session('error') }}</x-alert>
        @endif

        @error('status')
            <x-alert type="error" dismissible>{{ $message }}</x-alert>
        @enderror
    </div>
@endif
