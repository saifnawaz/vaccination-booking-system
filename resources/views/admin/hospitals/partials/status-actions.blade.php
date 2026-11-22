{{--
    Approve / decline / deactivate / reactivate buttons for one hospital.
    Moves that take a hospital offline (to inactive) ask for confirmation first.
--}}
@use('App\Enums\HospitalStatus')
@php($size ??= 'sm')

<div class="flex flex-wrap gap-2">
    @foreach ($hospital->status->allowedTransitions() as $next)
        @php($label = $hospital->status->actionLabel($next))

        @if ($next === HospitalStatus::Inactive)
            @php($dialog = 'hospital-'.$hospital->id.'-'.$next->value)
            <x-button type="button" variant="secondary" :size="$size"
                      x-data x-on:click="$dispatch('open-modal', '{{ $dialog }}')">
                {{ $label }}
            </x-button>

            <x-confirm-dialog
                :name="$dialog"
                :title="$label.' '.$hospital->name.'?'"
                :action="route('admin.hospitals.status', $hospital)"
                method="PATCH"
                :confirm-label="$label"
            >
                @if ($hospital->status === HospitalStatus::Pending)
                    The registration will be marked inactive and the hospital won't be able to log in.
                    You can reactivate it later if this was a mistake.
                @else
                    The hospital will be signed out, won't be able to log in, and will stop appearing to parents.
                    You can reactivate it at any time.
                    @php($open = $hospital->appointments()->open()->count())
                    @if ($open > 0)
                        <strong class="mt-2 block text-ink">{{ $open }} open {{ str('appointment')->plural($open) }} will stay booked but can't be handled while it is inactive.</strong>
                    @endif
                @endif
                <x-slot:fields>
                    <input type="hidden" name="status" value="{{ $next->value }}">
                </x-slot:fields>
            </x-confirm-dialog>
        @else
            <form method="POST" action="{{ route('admin.hospitals.status', $hospital) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $next->value }}">
                <x-button :size="$size">{{ $label }}</x-button>
            </form>
        @endif
    @endforeach
</div>
