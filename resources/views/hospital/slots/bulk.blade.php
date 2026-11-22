<x-app-layout title="Create slots in bulk">
    <x-page-header title="Create slots in bulk"
                   description="Set your opening hours once and we'll create every slot. Times you already have are skipped.">
        <x-slot:breadcrumb>
            <a href="{{ route('hospital.slots.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Time slots
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    @php
        $days = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
        $checkedDays = array_map('intval', old('weekdays', [1, 2, 3, 4, 5, 6]));
    @endphp

    {{-- The estimate is a convenience only; the server works out and reports the real numbers.
         x-data sits on a plain div: Blade directives like @js() aren't compiled inside component attributes. --}}
    <div class="max-w-2xl"
            x-data="{
                from: @js(old('date_from', today()->toDateString())),
                to: @js(old('date_to', today()->addWeeks(4)->toDateString())),
                start: @js(old('start_time', '09:00')),
                end: @js(old('end_time', '13:00')),
                minutes: @js((int) old('slot_minutes', 30)),
                days: @js($checkedDays),
                get estimate() {
                    const toMinutes = t => { const [h, m] = (t || '0:0').split(':').map(Number); return h * 60 + m; };
                    const perDay = Math.max(0, Math.floor((toMinutes(this.end) - toMinutes(this.start)) / this.minutes));
                    if (!this.from || !this.to || this.to < this.from) return 0;
                    let count = 0;
                    for (let d = new Date(this.from + 'T00:00'); d <= new Date(this.to + 'T00:00'); d.setDate(d.getDate() + 1)) {
                        if (this.days.includes(((d.getDay() + 6) % 7) + 1)) count += perDay;
                    }
                    return count;
                },
            }">
    <x-card>
        <form method="POST" action="{{ route('hospital.slots.bulk.store') }}" class="space-y-6">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field name="date_from" label="First day" type="date" required :min="today()->toDateString()"
                         :value="today()->toDateString()" x-model="from" />
                <x-field name="date_to" label="Last day" type="date" required :min="today()->toDateString()"
                         :value="today()->addWeeks(4)->toDateString()" x-model="to"
                         hint="Up to {{ $maxDays }} days at a time." />
            </div>

            <fieldset>
                <legend class="text-sm font-medium text-ink">Days of the week <span class="text-danger-700" aria-hidden="true">*</span></legend>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($days as $number => $label)
                        <label class="relative">
                            <input type="checkbox" name="weekdays[]" value="{{ $number }}" class="peer sr-only"
                                   x-model.number="days" @checked(in_array($number, $checkedDays, true))>
                            <span class="inline-flex min-w-14 cursor-pointer items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-line-input
                                         peer-checked:bg-primary-700 peer-checked:text-white peer-checked:ring-primary-700
                                         peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary-600">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('weekdays')" />
            </fieldset>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field name="start_time" label="Opens" type="time" required value="09:00" x-model="start" />
                <x-field name="end_time" label="Closes" type="time" required value="13:00" x-model="end" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-select-field name="slot_minutes" label="Slot length" required :value="old('slot_minutes', 30)"
                                :options="collect($lengths)->mapWithKeys(fn ($m) => [$m => $m.' minutes'])->all()"
                                x-model.number="minutes" />
                <x-field name="capacity" label="Children per slot" type="number" required min="1"
                         :max="config('vaccination.slots.max_capacity')" value="4" inputmode="numeric" />
            </div>

            <p class="rounded-xl bg-primary-50 px-4 py-3 text-sm text-primary-800" aria-live="polite">
                This will create up to <strong x-text="estimate"></strong> slots.
            </p>

            <div class="flex gap-3">
                <x-button>Create slots</x-button>
                <x-button :href="route('hospital.slots.index')" variant="ghost">Cancel</x-button>
            </div>
        </form>
    </x-card>
    </div>
</x-app-layout>
