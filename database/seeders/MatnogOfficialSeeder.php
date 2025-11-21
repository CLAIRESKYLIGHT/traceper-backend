<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Official;
use App\Models\Barangay;

class MatnogOfficialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Municipal-level officials (no barangay_id)
        $municipalOfficials = [
            [
                'name' => 'Hon. Mayor',
                'position' => 'Municipal Mayor',
                'term' => '2022-2025',
            ],
            [
                'name' => 'Hon. Vice Mayor',
                'position' => 'Vice Mayor',
                'term' => '2022-2025',
            ],
        ];

        // Add 8 Municipal Councilors
        for ($i = 1; $i <= 8; $i++) {
            $municipalOfficials[] = [
                'name' => "Hon. Councilor {$i}",
                'position' => 'Municipal Councilor',
                'term' => '2022-2025',
            ];
        }

        // Insert municipal officials (explicitly set barangay_id to null for municipal level)
        foreach ($municipalOfficials as $official) {
            Official::create(array_merge($official, ['barangay_id' => null]));
        }

        // Barangay-level officials (Barangay Captains)
        $barangays = Barangay::all();

        foreach ($barangays as $barangay) {
            // Barangay Captain
            Official::create([
                'name' => "Hon. Kap. {$barangay->name}",
                'position' => 'Barangay Captain',
                'term' => '2023-2026',
                'barangay_id' => $barangay->id,
            ]);

            // Barangay Secretary (optional)
            Official::create([
                'name' => "Brgy. Sec. {$barangay->name}",
                'position' => 'Barangay Secretary',
                'term' => '2023-2026',
                'barangay_id' => $barangay->id,
            ]);

            // Barangay Treasurer (optional)
            Official::create([
                'name' => "Brgy. Treas. {$barangay->name}",
                'position' => 'Barangay Treasurer',
                'term' => '2023-2026',
                'barangay_id' => $barangay->id,
            ]);
        }
    }
}

