<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contractor;

class MatnogContractorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contractors = [
            [
                'name' => 'Bicol Builders & Construction Co.',
                'owner_name' => 'Engr. Roberto Garcia',
                'business_registration' => 'DTI-2020-123456',
                'contact_info' => '0917-123-4567',
                'address' => 'Sorsogon City, Sorsogon'
            ],
            [
                'name' => 'Matnog Infrastructure Development Corp.',
                'owner_name' => 'Engr. Maria Santos',
                'business_registration' => 'SEC-2019-789012',
                'contact_info' => '0918-234-5678',
                'address' => 'Poblacion, Matnog, Sorsogon'
            ],
            [
                'name' => 'Southern Luzon Construction Services',
                'owner_name' => 'Engr. Juan Dela Cruz',
                'business_registration' => 'DTI-2021-345678',
                'contact_info' => '0919-345-6789',
                'address' => 'Legazpi City, Albay'
            ],
            [
                'name' => 'Aqua Solutions Philippines',
                'owner_name' => 'Engr. Ana Rodriguez',
                'business_registration' => 'SEC-2020-901234',
                'contact_info' => '0920-456-7890',
                'address' => 'Naga City, Camarines Sur'
            ],
            [
                'name' => 'Flood Control Engineering Services',
                'owner_name' => 'Engr. Carlos Mendoza',
                'business_registration' => 'DTI-2018-567890',
                'contact_info' => '0921-567-8901',
                'address' => 'Iriga City, Camarines Sur'
            ],
            [
                'name' => 'Public Works & Maintenance Co.',
                'owner_name' => 'Engr. Liza Fernandez',
                'business_registration' => 'SEC-2021-234567',
                'contact_info' => '0922-678-9012',
                'address' => 'Sorsogon City, Sorsogon'
            ],
        ];

        foreach ($contractors as $contractor) {
            Contractor::create($contractor);
        }
    }
}

