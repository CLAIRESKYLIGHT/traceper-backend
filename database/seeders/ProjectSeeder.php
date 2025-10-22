<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Barangay;
use App\Models\Contractor;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $barangays = Barangay::all();
        $contractors = Contractor::all();

        if ($barangays->isEmpty() || $contractors->isEmpty()) {
            $this->command->info('⚠️ Please seed Barangays and Contractors first!');
            return;
        }

        $projects = [
            [
                'title' => 'Barangay Hall Renovation - Mabini',
                'description' => 'Renovation of existing Barangay Hall for improved service delivery.',
                'objectives' => 'Enhance accessibility and improve office space.',
                'budget_allocated' => 500000.00,
                'amount_spent' => 300000.00,
                'status' => 'In Progress',
                'barangay_id' => $barangays->first()->id,
                'contractor_id' => $contractors->first()->id,
                'start_date' => now()->subDays(30),
                'estimated_completion_date' => now()->addDays(60),
            ],
            [
                'title' => 'Road Improvement - San Roque',
                'description' => 'Concrete paving of main barangay road to improve transport and access.',
                'objectives' => 'Provide safer, smoother road for community.',
                'budget_allocated' => 1200000.00,
                'amount_spent' => 600000.00,
                'status' => 'In Progress',
                'barangay_id' => $barangays[2]->id,
                'contractor_id' => $contractors[1]->id,
                'start_date' => now()->subDays(15),
                'estimated_completion_date' => now()->addDays(45),
            ],
            [
                'title' => 'Drainage System - Del Pilar',
                'description' => 'Construction of new drainage canals to prevent flooding.',
                'objectives' => 'Improve water flow and prevent property damage.',
                'budget_allocated' => 800000.00,
                'amount_spent' => 800000.00,
                'status' => 'Completed',
                'barangay_id' => $barangays[11]->id,
                'contractor_id' => $contractors[2]->id,
                'start_date' => now()->subDays(90),
                'estimated_completion_date' => now()->subDays(10),
                'actual_completion_date' => now()->subDays(10),
            ],
        ];

        foreach ($projects as $data) {
            $project = Project::create($data);

            // Example transactions
            Transaction::create([
                'project_id' => $project->id,
                'amount' => $project->amount_spent / 2,
                'description' => 'Initial materials and labor',
                'transaction_date' => now()->subDays(20),
            ]);

            Transaction::create([
                'project_id' => $project->id,
                'amount' => $project->amount_spent / 2,
                'description' => 'Final payment and inspection',
                'transaction_date' => now()->subDays(5),
            ]);
        }
    }
}
