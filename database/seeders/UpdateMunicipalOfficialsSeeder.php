<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Official;

class UpdateMunicipalOfficialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder updates/creates municipal-level officials:
     * - Mayor
     * - Vice Mayor
     * - 8 Municipal Councilors
     */
    public function run(): void
    {
        // Municipal-level officials (barangay_id = null)
        
        // 1. Mayor
        Official::updateOrCreate(
            [
                'position' => 'Municipal Mayor',
                'barangay_id' => null, // Municipal level
            ],
            [
                'name' => 'Bobet Lee Rodrigueza',
                'term' => '2022-2025', // Update term as needed
            ]
        );

        // 2. Vice Mayor
        Official::updateOrCreate(
            [
                'position' => 'Vice Mayor',
                'barangay_id' => null, // Municipal level
            ],
            [
                'name' => 'Jay Ubaldo',
                'term' => '2022-2025', // Update term as needed
            ]
        );

        // 3. Municipal Councilors (8 councilors)
        $councilors = [
            'Cecil Ubaldo',
            'Mondoy Garay',
            'Epoy Barlin',
            'Mercy Gata',
            'Atorni Junnar Garcia',
            'Mac-Mac Ubaldo Bilazon',
            'Pobie Sabado',
            'Alan Gacis',
        ];

        // Delete existing municipal councilors to avoid duplicates
        Official::where('position', 'Municipal Councilor')
            ->whereNull('barangay_id')
            ->delete();

        // Create the 8 councilors
        foreach ($councilors as $index => $councilorName) {
            Official::create([
                'name' => $councilorName,
                'position' => 'Municipal Councilor',
                'barangay_id' => null, // Municipal level
                'term' => '2022-2025', // Update term as needed
            ]);
        }

        $this->command->info('✅ Municipal officials updated successfully!');
        $this->command->info('   - Mayor: Bobet Lee Rodrigueza');
        $this->command->info('   - Vice Mayor: Jay Ubaldo');
        $this->command->info('   - 8 Municipal Councilors created');
    }
}

