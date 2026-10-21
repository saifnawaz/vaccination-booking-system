<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Hospital = 'hospital';
    case Parent = 'parent';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Hospital => 'Hospital',
            self::Parent => 'Parent',
        };
    }

    /**
     * Name of the route each role lands on after logging in.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Hospital => 'hospital.dashboard',
            self::Parent => 'parent.dashboard',
        };
    }
}
