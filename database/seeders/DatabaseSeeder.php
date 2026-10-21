<?php

namespace Database\Seeders;

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
        ]);
    }
}
