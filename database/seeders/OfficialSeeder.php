<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Official;
use App\Models\Barangay;

class OfficialSeeder extends Seeder
{
    public function run(): void
    {
        // 🏛️ Municipal-level officials
        $municipalOfficials = [
            [
                'name' => 'Hon. Juan Dela Cruz',
                'position' => 'Municipal Mayor',
                'type' => 'elected',
                'contact' => '0995-111-2222',
                'photo' => null,
            ],
            [
                'name' => 'Hon. Maria Santos',
                'position' => 'Vice Mayor',
                'type' => 'elected',
                'contact' => '0995-333-4444',
                'photo' => null,
            ],
        ];

        // Add 8 councilors
        for ($i = 1; $i <= 8; $i++) {
            $municipalOfficials[] = [
                'name' => 'Hon. Councilor '.$i.' Cruz',
                'position' => 'Municipal Councilor',
                'type' => 'elected',
                'contact' => '0917-800-'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'photo' => null,
            ];
        }

        // SK Federation Chairperson
        $municipalOfficials[] = [
            'name' => 'Hon. Kevin Garcia',
            'position' => 'SK Federation Chairperson',
            'type' => 'elected',
            'contact' => '0917-999-8888',
            'photo' => null,
        ];

        // Insert municipal officials
        foreach ($municipalOfficials as $official) {
            Official::create($official);
        }

        // 🏘️ Barangay-level officials (Barangay Captains & SK Chairs)
        $barangays = Barangay::all();

        foreach ($barangays as $barangay) {
            // Barangay Captain
            Official::create([
                'name' => 'Hon. Captain '.$barangay->name,
                'position' => 'Barangay Captain',
                'type' => 'elected',
                'contact' => '0908-'.rand(1000000, 9999999),
                'photo' => null,
                'barangay_id' => $barangay->id,
            ]);

            // SK Chairperson
            Official::create([
                'name' => 'Hon. SK Chair '.$barangay->name,
                'position' => 'SK Chairperson',
                'type' => 'elected',
                'contact' => '0909-'.rand(1000000, 9999999),
                'photo' => null,
                'barangay_id' => $barangay->id,
            ]);
        }
    }
}
