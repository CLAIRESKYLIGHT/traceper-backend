<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contractor;

class ContractorSeeder extends Seeder
{
    public function run(): void
    {
        $contractors = [
            [
                'name' => 'BuildRight Construction',
                'owner_name' => 'Engr. Jose Dela Cruz',
                'business_registration' => 'BR-102938',
                'contact_info' => '0998-111-2222',
                'address' => 'Poblacion, San Isidro'
            ],
            [
                'name' => 'SolidWorks Builders',
                'owner_name' => 'Engr. Maria Santos',
                'business_registration' => 'SW-874512',
                'contact_info' => '0917-333-4444',
                'address' => 'Del Pilar, San Isidro'
            ],
            [
                'name' => 'PrimeCore Development',
                'owner_name' => 'Engr. Leon Reyes',
                'business_registration' => 'PC-564789',
                'contact_info' => '0919-555-6666',
                'address' => 'San Vicente, San Isidro'
            ]
        ];

        foreach ($contractors as $contractor) {
            Contractor::create($contractor);
        }
    }
}
