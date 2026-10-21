<?php

namespace Database\Seeders;

use App\Enums\HospitalStatus;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo accounts: 1 admin, 3 hospitals (2 active, 1 inactive) and 10 parents.
 * All names, hospitals, addresses and numbers are invented; all emails are @test.com.
 * Password for every account: "password".
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Demo Administrator',
            'email' => 'admin@test.com',
            'phone' => null,
        ]);

        $hospitals = [
            ['Green Crescent Children\'s Clinic', '12 Canal View Road', 'Lahore', '042-0000101', HospitalStatus::Active],
            ['Sunrise Family Health Centre', '45 Harbour Street', 'Karachi', '021-0000202', HospitalStatus::Active],
            ['Margalla Community Hospital', '7 Hillside Avenue', 'Islamabad', '051-0000303', HospitalStatus::Inactive],
        ];

        foreach ($hospitals as $i => [$name, $address, $city, $phone, $status]) {
            $number = $i + 1;

            $user = User::factory()->hospital()->create([
                'name' => "Clinic Manager {$number}",
                'email' => "hospital{$number}@test.com",
                'phone' => null,
            ]);

            Hospital::factory()->for($user)->create([
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'phone' => $phone,
                'license_number' => sprintf('DEMO-LIC-%03d', $number),
                'status' => $status,
                'approved_by' => $admin->id,
                'approved_at' => now()->subMonths(3 - $i),
            ]);
        }

        $parents = [
            'Ayesha Khan', 'Bilal Ahmed', 'Sana Malik', 'Usman Tariq', 'Hira Siddiqui',
            'Imran Qureshi', 'Fatima Raza', 'Hamza Sheikh', 'Zainab Iqbal', 'Omar Farooq',
        ];

        foreach ($parents as $i => $name) {
            $number = $i + 1;

            User::factory()->parent()->create([
                'name' => $name,
                'email' => "parent{$number}@test.com",
                'phone' => sprintf('0300-00000%02d', $number),
            ]);
        }
    }
}
