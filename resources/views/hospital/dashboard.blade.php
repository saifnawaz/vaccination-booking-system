<x-app-layout title="Overview">
    <x-page-header :title="$hospital->name" description="Your hospital's overview." />

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Hospital details</h2>
                    <p class="mt-1 text-sm text-ink-muted">Parents see these details when they choose where to book.</p>
                </div>
                <x-badge :tone="$hospital->status->tone()">{{ $hospital->status->label() }}</x-badge>
            </div>

            <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                @foreach ([
                    'Address' => $hospital->address.', '.$hospital->city,
                    'Phone' => $hospital->phone,
                ] as $term => $detail)
                    <div>
                        <dt class="text-sm font-medium text-ink-muted">{{ $term }}</dt>
                        <dd class="mt-0.5 text-ink">{{ $detail }}</dd>
                    </div>
                @endforeach
            </dl>

            <x-button :href="route('hospital.profile.edit')" variant="secondary" class="mt-6">Edit hospital profile</x-button>
        </x-card>

        <x-card>
            <h2 class="text-base font-semibold text-ink">Getting set up</h2>
            <ol class="mt-4 space-y-4 text-sm">
                <li class="flex items-start gap-3">
                    <x-heroicon-o-check-circle class="size-5 shrink-0 text-success-700" aria-hidden="true" />
                    <span class="text-ink">Hospital approved{{ $hospital->approved_at ? ' on '.$hospital->approved_at->format('j M Y') : '' }}</span>
                </li>
                <li class="flex items-start gap-3">
                    <x-heroicon-o-clock class="size-5 shrink-0 text-ink-subtle" aria-hidden="true" />
                    <span class="text-ink-muted">Add vaccine stock: coming in the next update</span>
                </li>
                <li class="flex items-start gap-3">
                    <x-heroicon-o-clock class="size-5 shrink-0 text-ink-subtle" aria-hidden="true" />
                    <span class="text-ink-muted">Open appointment slots: coming in the next update</span>
                </li>
            </ol>
        </x-card>
    </div>
</x-app-layout>
