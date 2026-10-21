<div class="space-y-5">
    <x-field name="name" label="Name" :value="$vaccine->name" required />

    <x-field name="code" label="Code" :value="$vaccine->code" required class="max-w-xs font-mono uppercase"
             hint="A short unique code, e.g. PENTA. Letters, numbers and dashes." />

    <x-textarea-field name="description" label="Description" :value="$vaccine->description"
                      hint="Shown to parents and hospitals." />

    <input type="hidden" name="is_active" value="0">
    <x-checkbox name="is_active" value="1" label="Active"
                :checked="old('is_active', $vaccine->is_active)"
                hint="Inactive vaccines can't receive new stock or be booked, but stay on children's schedules." />
</div>
