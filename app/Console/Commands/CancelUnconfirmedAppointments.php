<?php

namespace App\Console\Commands;

use App\Services\AppointmentService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('appointments:cancel-unconfirmed')]
#[Description('Cancel pending bookings whose time has started without the hospital confirming them')]
class CancelUnconfirmedAppointments extends Command
{
    public function handle(AppointmentService $appointments): int
    {
        $count = $appointments->cancelUnconfirmedPastStart();

        $this->info("Cancelled {$count} unconfirmed ".str('appointment')->plural($count).'.');

        return self::SUCCESS;
    }
}
