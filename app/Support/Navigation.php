<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\User;

/**
 * The main menu for each role, in one place. Later phases add items here.
 */
class Navigation
{
    /**
     * @return list<array{label: string, route: string, active: string, icon: string}>
     */
    public static function for(User $user): array
    {
        return match ($user->role) {
            Role::Admin => [
                self::item('Overview', 'admin.dashboard', 'admin.dashboard', 'heroicon-o-home'),
                self::item('Hospitals', 'admin.hospitals.index', 'admin.hospitals.*', 'heroicon-o-building-office-2'),
                self::item('Vaccines', 'admin.vaccines.index', 'admin.vaccines.*', 'heroicon-o-beaker'),
                self::item('Administrators', 'admin.admins.index', 'admin.admins.*', 'heroicon-o-shield-check'),
            ],
            Role::Hospital => [
                self::item('Overview', 'hospital.dashboard', 'hospital.dashboard', 'heroicon-o-home'),
                self::item('Appointments', 'hospital.appointments.index', 'hospital.appointments.*', 'heroicon-o-clipboard-document-check'),
                self::item('Stock', 'hospital.stock.index', 'hospital.stock.*', 'heroicon-o-archive-box'),
                self::item('Time slots', 'hospital.slots.index', 'hospital.slots.*', 'heroicon-o-calendar-days'),
                self::item('Hospital profile', 'hospital.profile.edit', 'hospital.profile.*', 'heroicon-o-building-office-2'),
            ],
            Role::Parent => [
                self::item('Home', 'parent.dashboard', 'parent.dashboard', 'heroicon-o-home'),
                self::item('Children', 'parent.children.index', 'parent.children.*', 'heroicon-o-heart'),
                self::item('Book', 'parent.book', 'parent.book*', 'heroicon-o-plus-circle'),
                self::item('Appointments', 'parent.appointments.index', 'parent.appointments.*', 'heroicon-o-calendar-days'),
                self::item('Account', 'profile.edit', 'profile.*', 'heroicon-o-user-circle'),
            ],
        };
    }

    /**
     * @return array{label: string, route: string, active: string, icon: string}
     */
    private static function item(string $label, string $route, string $active, string $icon): array
    {
        return compact('label', 'route', 'active', 'icon');
    }
}
