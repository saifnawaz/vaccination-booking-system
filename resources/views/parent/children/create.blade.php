<x-app-layout title="Add a child">
    <x-page-header title="Add a child"
                   description="We'll build the full vaccination schedule from the date of birth.">
        <x-slot:breadcrumb>
            <a href="{{ route('parent.children.index') }}" class="inline-flex items-center gap-1 font-medium text-primary-700 hover:underline">
                <x-heroicon-o-chevron-left class="size-4" aria-hidden="true" /> Children
            </a>
        </x-slot:breadcrumb>
    </x-page-header>

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('parent.children.store') }}">
            @csrf
            @include('parent.children.partials.form')

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <x-button size="lg">Add child and see schedule</x-button>
                <x-button :href="route('parent.children.index')" variant="ghost" size="lg">Cancel</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
