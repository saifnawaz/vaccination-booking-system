<x-app-layout :title="'Edit '.$vaccine->name">
    <x-page-header :title="$vaccine->name">
        <x-slot:breadcrumb>
            <a href="{{ route('admin.vaccines.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Vaccine catalog
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <form method="POST" action="{{ route('admin.vaccines.update', $vaccine) }}">
                @csrf
                @method('PUT')
                @include('admin.vaccines.partials.form')
                <div class="mt-6 flex gap-3">
                    <x-button>Save changes</x-button>
                    <x-button :href="route('admin.vaccines.index')" variant="ghost">Cancel</x-button>
                </div>
            </form>
        </x-card>

        <div class="space-y-6">
            <x-card>
                <h2 class="text-base font-semibold text-ink">In the routine schedule</h2>
                @if ($vaccine->scheduleDoses->isEmpty())
                    <p class="mt-2 text-sm text-ink-muted">This vaccine isn't part of the routine schedule.</p>
                @else
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($vaccine->scheduleDoses as $dose)
                            <li class="flex justify-between gap-3">
                                <span class="text-ink">{{ $dose->label }}</span>
                                <span class="text-ink-muted">{{ \App\Models\VaccineSchedule::ageLabel($dose->age_days) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            <x-card>
                <h2 class="text-base font-semibold text-ink">Delete vaccine</h2>
                @can('delete', $vaccine)
                    <p class="mt-2 text-sm text-ink-muted">It will be removed from the catalog. Existing stock records are kept.</p>
                    <x-button type="button" variant="danger" class="mt-4" x-data x-on:click="$dispatch('open-modal', 'delete-vaccine')">
                        Delete {{ $vaccine->name }}
                    </x-button>
                    <x-confirm-dialog name="delete-vaccine" :title="'Delete '.$vaccine->name.'?'"
                                      :action="route('admin.vaccines.destroy', $vaccine)" method="DELETE" confirm-label="Delete">
                        Hospitals will no longer be able to add stock of this vaccine.
                    </x-confirm-dialog>
                @else
                    <p class="mt-2 text-sm text-ink-muted">
                        Vaccines in the routine schedule can't be deleted because children's schedules are built from them.
                        Untick <strong>Active</strong> to stop new stock and bookings instead.
                    </p>
                @endcan
            </x-card>
        </div>
    </div>
</x-app-layout>
