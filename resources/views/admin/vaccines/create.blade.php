<x-app-layout title="Add vaccine">
    <x-page-header title="Add vaccine">
        <x-slot:breadcrumb>
            <a href="{{ route('admin.vaccines.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Vaccine catalog
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-alert type="info" class="mb-6 max-w-2xl">
        New vaccines are added to the catalog only. The routine schedule is maintained in <code class="font-mono">config/epi.php</code>.
    </x-alert>

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('admin.vaccines.store') }}">
            @csrf
            @include('admin.vaccines.partials.form')
            <div class="mt-6 flex gap-3">
                <x-button>Add vaccine</x-button>
                <x-button :href="route('admin.vaccines.index')" variant="ghost">Cancel</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
