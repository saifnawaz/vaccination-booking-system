{{-- Lifecycle buttons for one appointment ($appointment). Destructive ones confirm first. --}}
@use('App\Enums\AppointmentStatus')
@php
    $status = $appointment->status;
    $slot = $appointment->timeSlot;
    $who = $appointment->doseLabel().' for '.$appointment->child->first_name;
    $size ??= 'sm';
@endphp

<div class="flex flex-wrap gap-2">
    @if ($status === AppointmentStatus::Pending)
        <form method="POST" action="{{ route('hospital.appointments.approve', $appointment) }}">
            @csrf @method('PATCH')
            <x-button :size="$size">Confirm <span class="sr-only">{{ $who }}</span></x-button>
        </form>
    @endif

    @if ($status === AppointmentStatus::Approved && ! $slot->date->isFuture())
        <form method="POST" action="{{ route('hospital.appointments.complete', $appointment) }}">
            @csrf @method('PATCH')
            <x-button :size="$size" variant="primary">
                <x-heroicon-o-check-circle class="size-4" aria-hidden="true" /> Given <span class="sr-only">{{ $who }}</span>
            </x-button>
        </form>
    @endif

    @if ($status === AppointmentStatus::Approved && $slot->hasStarted())
        <x-button type="button" variant="secondary" :size="$size" x-data x-on:click="$dispatch('open-modal', 'no-show-{{ $appointment->id }}')">
            Didn't come <span class="sr-only">{{ $who }}</span>
        </x-button>
        <x-confirm-dialog :name="'no-show-'.$appointment->id" title="Mark as missed?"
                          :action="route('hospital.appointments.no-show', $appointment)" method="PATCH" confirm-label="Mark as missed">
            {{ $appointment->child->fullName() }} didn't come for {{ $appointment->doseLabel() }}. The reserved dose goes back into your stock.
        </x-confirm-dialog>
    @endif

    @if ($status->isOpen())
        @php($bag = 'reject'.$appointment->id)
        <x-button type="button" variant="ghost" :size="$size" class="text-danger-700 hover:bg-danger-50"
                  x-data x-on:click="$dispatch('open-modal', 'reject-{{ $appointment->id }}')">
            Decline <span class="sr-only">{{ $who }}</span>
        </x-button>
        <x-modal :name="'reject-'.$appointment->id" max-width="md" :labelledby="'reject-'.$appointment->id.'-title'"
                 :show="$errors->getBag($bag)->isNotEmpty()" focusable>
            <form method="POST" action="{{ route('hospital.appointments.reject', $appointment) }}" class="p-6">
                @csrf @method('PATCH')
                <h2 id="reject-{{ $appointment->id }}-title" class="text-lg font-semibold text-ink">Decline this appointment?</h2>
                <p class="mt-2 text-sm text-ink-muted">
                    {{ $who }} on {{ $slot->date->format('j F') }}. The parent will see your reason, and the reserved dose goes back into your stock.
                </p>
                <div class="mt-5">
                    <x-input-label :for="'reason-'.$appointment->id" value="Reason for the parent" required />
                    <textarea id="reason-{{ $appointment->id }}" name="reason" rows="3" required maxlength="500"
                              class="mt-1.5 block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs focus:border-primary-600 focus:ring-primary-600">{{ old('reason') }}</textarea>
                    <x-input-error :messages="$errors->getBag($bag)->get('reason')" />
                </div>
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-button variant="secondary" type="button" x-on:click="$dispatch('close')">Keep appointment</x-button>
                    <x-button variant="danger">Decline</x-button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
