<x-app-layout :title="$hospital->name">
    <x-page-header :title="$hospital->name">
        <x-slot:breadcrumb>
            <a href="{{ route('admin.hospitals.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> All hospitals
            </a>
        </x-slot:breadcrumb>
        <x-slot:actions>
            @include('admin.hospitals.partials.status-actions', ['hospital' => $hospital, 'size' => 'md'])
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <h2 class="text-base font-semibold text-ink">Hospital details</h2>
            <dl class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                @foreach ([
                    'Address' => $hospital->address,
                    'City' => $hospital->city,
                    'Phone' => $hospital->phone,
                    'Licence number' => $hospital->license_number ?? 'Not provided',
                ] as $term => $detail)
                    <div>
                        <dt class="text-sm font-medium text-ink-muted">{{ $term }}</dt>
                        <dd class="mt-0.5 text-ink">{{ $detail }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>

        <div class="space-y-6">
            <x-card>
                <h2 class="text-base font-semibold text-ink">Status</h2>
                <x-badge :tone="$hospital->status->tone()" class="mt-3">{{ $hospital->status->label() }}</x-badge>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="font-medium text-ink-muted">Registered</dt>
                        <dd class="text-ink">{{ $hospital->created_at->format('j M Y, g:i a') }}</dd>
                    </div>
                    @if ($hospital->approved_at)
                        <div>
                            <dt class="font-medium text-ink-muted">Approved</dt>
                            <dd class="text-ink">
                                {{ $hospital->approved_at->format('j M Y') }}
                                @if ($hospital->approver)
                                    by {{ $hospital->approver->name }}
                                @endif
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            <x-card>
                <h2 class="text-base font-semibold text-ink">Contact person</h2>
                <p class="mt-3 text-ink">{{ $hospital->user->name }}</p>
                <p class="text-sm text-ink-muted">{{ $hospital->user->email }}</p>
            </x-card>
        </div>
    </div>
</x-app-layout>
