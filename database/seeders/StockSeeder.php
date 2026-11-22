<?php

namespace Database\Seeders;

use App\Enums\HospitalStatus;
use App\Models\Hospital;
use App\Models\HospitalStock;
use App\Models\Vaccine;
use Illuminate\Database\Seeder;

/**
 * Demo vaccine batches. Batch numbers are invented.
 *
 * Deliberate warnings at the first active hospital:
 *   - one EXPIRED OPV batch still holding doses
 *   - PCV LOW: a single batch of 6 doses (below the low-stock threshold)
 *   - one Pentavalent batch expiring within 30 days
 */
class StockSeeder extends Seeder
{
    public function run(): void
    {
        $vaccines = Vaccine::all()->keyBy('code');
        [$first, $second] = Hospital::where('status', HospitalStatus::Active)->orderBy('id')->take(2)->get();
        $inactive = Hospital::where('status', HospitalStatus::Inactive)->first();

        $batches = [
            // [hospital, vaccine code, batch number, expiry (days from today), quantity]
            [$first, 'BCG', 'LHR-BCG-2601', 240, 80],
            [$first, 'HEPB', 'LHR-HEPB-2601', 300, 60],
            [$first, 'OPV', 'LHR-OPV-2601', 180, 120],
            [$first, 'OPV', 'LHR-OPV-2509', -10, 15],     // expired, still on hand
            [$first, 'PENTA', 'LHR-PENTA-2602', 20, 25],  // expiring soon
            [$first, 'PENTA', 'LHR-PENTA-2604', 270, 90],
            [$first, 'PCV', 'LHR-PCV-2601', 150, 6],      // low stock
            [$first, 'ROTA', 'LHR-ROTA-2601', 200, 45],
            [$first, 'IPV', 'LHR-IPV-2601', 330, 50],
            [$first, 'MR', 'LHR-MR-2601', 120, 70],
            [$first, 'TCV', 'LHR-TCV-2601', 360, 40],

            [$second, 'BCG', 'KHI-BCG-2602', 210, 60],
            [$second, 'HEPB', 'KHI-HEPB-2602', 250, 40],
            [$second, 'OPV', 'KHI-OPV-2602', 160, 100],
            [$second, 'PENTA', 'KHI-PENTA-2602', 230, 75],
            [$second, 'PCV', 'KHI-PCV-2602', 190, 55],
            [$second, 'ROTA', 'KHI-ROTA-2602', 140, 35],
            [$second, 'IPV', 'KHI-IPV-2602', 300, 40],
            [$second, 'MR', 'KHI-MR-2602', 260, 50],

            [$inactive, 'OPV', 'ISB-OPV-2601', 120, 30],
            [$inactive, 'MR', 'ISB-MR-2601', 90, 20],
        ];

        foreach ($batches as [$hospital, $code, $number, $expiresInDays, $quantity]) {
            $batch = new HospitalStock([
                'vaccine_id' => $vaccines[$code]->id,
                'batch_number' => $number,
                'expiry_date' => today()->addDays($expiresInDays)->toDateString(),
                'quantity' => $quantity,
            ]);
            $batch->hospital_id = $hospital->id;
            $batch->save();
        }
    }
}
