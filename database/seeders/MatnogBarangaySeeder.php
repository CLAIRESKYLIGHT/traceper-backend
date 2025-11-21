<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barangay;

class MatnogBarangaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barangays = [
            ['name' => 'Balocawe', 'population' => 1006],
            ['name' => 'Banogao', 'population' => 542],
            ['name' => 'Banuangdaan', 'population' => 671],
            ['name' => 'Bariis', 'population' => 1355],
            ['name' => 'Bolo', 'population' => 1274],
            ['name' => 'Bon-Ot Big', 'population' => 1574],
            ['name' => 'Bon-Ot Small', 'population' => 366],
            ['name' => 'Cabagahan', 'population' => 492],
            ['name' => 'Calayuan', 'population' => 612],
            ['name' => 'Calintaan', 'population' => 1382],
            ['name' => 'Caloocan (Poblacion)', 'population' => 1061],
            ['name' => 'Calpi', 'population' => 96],
            ['name' => 'Camachiles (Poblacion)', 'population' => 1684],
            ['name' => 'Camcaman (Poblacion)', 'population' => 1123],
            ['name' => 'Coron-coron', 'population' => 1430],
            ['name' => 'Culasi', 'population' => 2159],
            ['name' => 'Gadgaron', 'population' => 2526],
            ['name' => 'Genablan Occidental', 'population' => 744],
            ['name' => 'Genablan Oriental', 'population' => 1094],
            ['name' => 'Hidhid', 'population' => 1300],
            ['name' => 'Laboy', 'population' => 779],
            ['name' => 'Lajong', 'population' => 634],
            ['name' => 'Mambajog', 'population' => 599],
            ['name' => 'Manjumlad', 'population' => 1122],
            ['name' => 'Manurabi', 'population' => 621],
            ['name' => 'Naburacan', 'population' => 498],
            ['name' => 'Paghuliran', 'population' => 526],
            ['name' => 'Pangi', 'population' => 683],
            ['name' => 'Pawa', 'population' => 1375],
            ['name' => 'Poropandan', 'population' => 910],
            ['name' => 'Santa Isabel', 'population' => 1385],
            ['name' => 'Sinalmacan', 'population' => 514],
            ['name' => 'Sinang-Atan', 'population' => 414],
            ['name' => 'Sinibaran', 'population' => 1081],
            ['name' => 'Sisigon', 'population' => 1708],
            ['name' => 'Sua', 'population' => 1364],
            ['name' => 'Sulangan', 'population' => 888],
            ['name' => 'Tablac (Poblacion)', 'population' => 1449],
            ['name' => 'Tabunan (Poblacion)', 'population' => 2010],
            ['name' => 'Tugas', 'population' => 938],
        ];

        foreach ($barangays as $barangay) {
            Barangay::create([
                'name' => $barangay['name'],
                'description' => "Barangay {$barangay['name']}, Matnog, Sorsogon - Population: {$barangay['population']} (2020 Census)",
                'population' => $barangay['population'],
                'status' => 'Active',
            ]);
        }
    }
}

