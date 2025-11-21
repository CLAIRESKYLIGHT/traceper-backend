<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            // Matnog Municipality Data
            MatnogBarangaySeeder::class,      // Must be first - creates 40 barangays
            MatnogContractorSeeder::class,    // Creates contractors
            MatnogOfficialSeeder::class,      // Creates officials (depends on barangays)
            MatnogProjectSeeder::class,       // Creates projects (depends on barangays, contractors, officials)
        ]);
    }
}
