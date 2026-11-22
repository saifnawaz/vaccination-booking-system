<?php

namespace App\Console\Commands;

use App\Services\DoseReminders;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reminders:send')]
#[Description('Notify parents about doses due within 7 days or overdue')]
class SendDoseReminders extends Command
{
    public function handle(DoseReminders $reminders): int
    {
        $sent = $reminders->send();

        $this->info("Sent {$sent['due']} due-soon and {$sent['overdue']} overdue reminders.");

        return self::SUCCESS;
    }
}
