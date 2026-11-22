<?php

namespace Database\Seeders;

use App\Services\DoseReminders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with fake demo data.
     * Every account uses an @test.com address and the password "password".
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            EpiScheduleSeeder::class,
            StockSeeder::class,
            TimeSlotSeeder::class,
            ChildSeeder::class,
            AppointmentSeeder::class,
        ]);

        // Give the demo parents their due/overdue reminders straight away,
        // as the daily reminders:send job would.
        app(DoseReminders::class)->send();
    }
}
