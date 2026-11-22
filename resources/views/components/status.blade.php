{{-- A status enum (DoseStatus, AppointmentStatus, HospitalStatus) as icon + label badge. --}}
@props(['status'])

<x-badge :tone="$status->tone()" :icon="$status->icon()" {{ $attributes }}>{{ $status->label() }}</x-badge>
