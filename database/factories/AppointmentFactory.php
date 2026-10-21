<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 *
 * Appointments must keep stock and slots consistent, so tests and seeders create
 * them through App\Services\AppointmentService rather than this factory.
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        throw new \LogicException('Create appointments with App\Services\AppointmentService::book().');
    }
}
