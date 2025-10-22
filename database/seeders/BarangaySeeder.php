<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Barangay;

class BarangaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barangays = [
            'Mabini', 'Maligaya', 'San Roque', 'Bagong Silang', 'Poblacion',
            'Sta. Cruz', 'San Jose', 'Tinago', 'San Vicente', 'San Pedro',
            'Sto. Niño', 'Del Pilar', 'San Rafael', 'Malinis', 'Magsaysay'
        ];

        foreach ($barangays as $name) {
            Barangay::create([
                'name' => 'Barangay ' . $name,
                'description' => 'A community under San Isidro Municipality.'
            ]);
        }
    }
}
