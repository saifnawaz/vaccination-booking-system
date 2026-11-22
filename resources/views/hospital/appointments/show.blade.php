<x-app-layout :title="$appointment->child->fullName()">
    @php($child = $appointment->child)

    <x-page-header :title="$child->fullName()"
                   :description="$appointment->doseLabel().' · '.$appointment->timeSlot->date->format('l j F Y').', '.$appointment->timeSlot->timeRange()">
        <x-slot:breadcrumb>
            <a href="{{ route('hospital.appointments.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Appointments
            </a>
        </x-slot:breadcrumb>
        <x-slot:actions>
            @include('hospital.appointments.partials.actions', ['appointment' => $appointment, 'size' => 'md'])
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card>
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-base font-semibold text-ink">This appointment</h2>
                    <x-badge :tone="$appointment->status->tone()">{{ $appointment->status->label() }}</x-badge>
                </div>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="font-medium text-ink-muted">Vaccine</dt>
                        <dd class="text-ink">{{ $appointment->doseLabel() }} · {{ $appointment->childDose->scheduleDose->vaccine->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-ink-muted">Reserved batch</dt>
                        <dd class="font-mono text-ink">{{ $appointment->hospitalStock->batch_number }}
                            <span class="font-sans text-ink-muted">(expires {{ $appointment->hospitalStock->expiry_date->format('j M Y') }})</span>
                        </dd>
                    </div>
                    @if ($visit->isNotEmpty())
                        <div class="sm:col-span-2">
                            <dt class="font-medium text-ink-muted">Also booked in this visit</dt>
                            <dd class="mt-1 flex flex-wrap gap-2">
                                @foreach ($visit as $other)
                                    <a href="{{ route('hospital.appointments.show', $other) }}">
                                        <x-badge :tone="$other->status->tone()">{{ $other->doseLabel() }}: {{ $other->status->label() }}</x-badge>
                                    </a>
                                @endforeach
                            </dd>
                        </div>
                    @endif
                    @if ($appointment->reason)
                        <div class="sm:col-span-2">
                            <dt class="font-medium text-ink-muted">Reason</dt>
                            <dd class="text-ink">{{ $appointment->reason }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            <section aria-labelledby="schedule-heading">
                <h2 id="schedule-heading" class="mb-3 text-lg font-semibold text-ink">Vaccination record</h2>
                <ul class="space-y-3">
                    @foreach ($schedule->doses()->filter(fn ($d) => $d->status !== \App\Enums\DoseStatus::Upcoming || $d->waitingFor === null) as $dose)
                        @include('parent.children.partials.dose-row', ['dose' => $dose])
                    @endforeach
                </ul>
            </section>
        </div>

        <div class="space-y-6">
            <x-card>
                <h2 class="text-base font-semibold text-ink">Child</h2>
                <dl class="mt-3 space-y-3 text-sm">
                    <div>
                        <dt class="font-medium text-ink-muted">Born</dt>
                        <dd class="text-ink">{{ $child->date_of_birth->format('j M Y') }} ({{ $child->ageLabel() }})</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-ink-muted">Gender</dt>
                        <dd class="text-ink">{{ $child->gender->label() }}</dd>
                    </div>
                    @if ($child->blood_group)
                        <div>
                            <dt class="font-medium text-ink-muted">Blood group</dt>
                            <dd class="text-ink">{{ $child->blood_group }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="font-medium text-ink-muted">Parent</dt>
                        <dd class="text-ink">{{ $child->parent->name }}@if ($child->parent->phone) · {{ $child->parent->phone }}@endif</dd>
                    </div>
                </dl>
                @if ($child->medical_notes)
                    <x-alert type="warning" title="Medical notes" class="mt-4">{{ $child->medical_notes }}</x-alert>
                @endif
            </x-card>

            <x-card>
                <h2 class="text-base font-semibold text-ink">Record an earlier dose</h2>
                <p class="mt-1 text-sm text-ink-muted">
                    Copy a dose from the child's vaccination card. It will show on their certificate as "recorded from previous records". No stock is used.
                </p>

                @if ($recordable->isEmpty())
                    <p class="mt-4 text-sm text-ink">Every dose due so far is already recorded.</p>
                @else
                    <form method="POST" action="{{ route('hospital.appointments.history', $appointment) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="child_dose_id" value="Dose" required />
                            <select id="child_dose_id" name="child_dose_id" required
                                    class="mt-1.5 block w-full rounded-lg border-line-input bg-surface text-ink shadow-xs focus:border-primary-600 focus:ring-primary-600">
                                @foreach ($recordable as $dose)
                                    <option value="{{ $dose->dose->id }}" @selected((int) old('child_dose_id') === $dose->dose->id)>{{ $dose->label() }} ({{ $dose->schedule()->ageLabel($dose->schedule()->age_days) }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->history->get('child_dose_id')" />
                        </div>
                        <div>
                            <x-input-label for="given_on" value="Date given" required />
                            <x-text-input id="given_on" name="given_on" type="date" required :value="old('given_on')"
                                          :min="$child->date_of_birth->toDateString()" :max="today()->toDateString()" class="mt-1.5" />
                            <x-input-error :messages="$errors->history->get('given_on')" />
                        </div>
                        <div>
                            <x-input-label for="batch_number" value="Batch number on the card" />
                            <x-text-input id="batch_number" name="batch_number" :value="old('batch_number')" maxlength="50" class="mt-1.5 font-mono" />
                            <x-input-error :messages="$errors->history->get('batch_number')" />
                        </div>
                        <x-button variant="secondary" class="w-full">
                            <x-heroicon-o-document-plus class="size-4" aria-hidden="true" /> Record dose
                        </x-button>
                    </form>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
