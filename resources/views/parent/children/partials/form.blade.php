@use('App\Enums\Gender')
@use('App\Models\Child')

<div class="space-y-5">
    <div class="grid gap-5 sm:grid-cols-2">
        <x-field name="first_name" label="First name" :value="$child->first_name" required autocomplete="off" />
        <x-field name="last_name" label="Last name" :value="$child->last_name" required autocomplete="off" />
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-field name="date_of_birth" label="Date of birth" type="date"
                 :value="$child->date_of_birth?->toDateString()" required
                 :max="today()->toDateString()"
                 :min="today()->subYears(config('vaccination.max_child_age_years'))->toDateString()"
                 hint="The schedule is worked out from this date." />

        <x-select-field name="gender" label="Gender" :value="$child->gender?->value" required placeholder="Choose…"
                        :options="collect(Gender::cases())->mapWithKeys(fn ($g) => [$g->value => $g->label()])->all()" />
    </div>

    <x-select-field name="blood_group" label="Blood group" :value="$child->blood_group" placeholder="Not known"
                    :options="array_combine(Child::BLOOD_GROUPS, Child::BLOOD_GROUPS)" class="sm:max-w-xs" />

    <x-textarea-field name="medical_notes" label="Medical notes" :value="$child->medical_notes"
                      hint="Optional. Allergies or conditions the vaccination team should know about." />
</div>
