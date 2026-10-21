<x-app-layout title="Home">
    <x-page-header :title="'Hello, '.\Illuminate\Support\Str::before($user->name, ' ')"
                   description="Your children's vaccinations, all in one place." />

    <x-empty-state title="No children added yet" icon="heroicon-o-heart">
        Soon you'll be able to add each child with their date of birth, and we'll build their
        full vaccination schedule automatically.
    </x-empty-state>
</x-app-layout>
