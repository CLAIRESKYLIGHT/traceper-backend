<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Barangay;
use App\Models\Contractor;
use App\Models\Official;
use App\Models\Transaction;
use Carbon\Carbon;

class MatnogProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barangays = Barangay::all();
        $contractors = Contractor::all();
        $officials = Official::whereNull('barangay_id')->get(); // Municipal officials

        if ($barangays->isEmpty() || $contractors->isEmpty()) {
            $this->command->info('⚠️ Please seed Barangays and Contractors first!');
            return;
        }

        // Real projects based on the provided data
        $projects = [
            [
                'title' => 'Construction of Level II Potable Water Supply - Hidhid',
                'description' => 'Construction of Level II Potable Water Supply System in Barangay Hidhid, Matnog, Sorsogon. This project aims to provide clean and safe drinking water to the residents.',
                'objectives' => 'Provide potable water supply to Barangay Hidhid residents. Improve public health and sanitation.',
                'barangay_id' => $barangays->where('name', 'Hidhid')->first()?->id ?? $barangays->random()->id,
                'contractor_id' => $contractors->where('name', 'LIKE', '%Aqua Solutions%')->first()?->id ?? $contractors->random()->id,
                'budget_allocated' => 2500000.00,
                'amount_spent' => 1800000.00,
                'status' => 'In Progress',
                'start_date' => Carbon::now()->subMonths(3),
                'estimated_completion_date' => Carbon::now()->addMonths(2),
            ],
            [
                'title' => 'Construction of Flood Control - Barangay Culasi',
                'description' => 'Construction of flood control infrastructure in Barangay Culasi, Matnog, Sorsogon to prevent flooding and protect properties during heavy rains.',
                'objectives' => 'Prevent flooding in Barangay Culasi. Protect lives and properties from flood damage.',
                'barangay_id' => $barangays->where('name', 'Culasi')->first()?->id ?? $barangays->random()->id,
                'contractor_id' => $contractors->where('name', 'LIKE', '%Flood Control%')->first()?->id ?? $contractors->random()->id,
                'budget_allocated' => 5000000.00,
                'amount_spent' => 3500000.00,
                'status' => 'In Progress',
                'start_date' => Carbon::now()->subMonths(4),
                'estimated_completion_date' => Carbon::now()->addMonths(3),
            ],
            [
                'title' => 'Repair/Rehabilitation of Public C.R. and Stage - Barangay Culasi',
                'description' => 'Repair and rehabilitation of public comfort room and stage facility in Barangay Culasi, Matnog, Sorsogon.',
                'objectives' => 'Improve public facilities. Enhance community gathering spaces.',
                'barangay_id' => $barangays->where('name', 'Culasi')->first()?->id ?? $barangays->random()->id,
                'contractor_id' => $contractors->where('name', 'LIKE', '%Public Works%')->first()?->id ?? $contractors->random()->id,
                'budget_allocated' => 800000.00,
                'amount_spent' => 800000.00,
                'status' => 'Completed',
                'start_date' => Carbon::now()->subMonths(6),
                'estimated_completion_date' => Carbon::now()->subMonths(2),
                'actual_completion_date' => Carbon::now()->subMonths(2),
            ],
            [
                'title' => 'Matnog Municipal Building Renovation',
                'description' => 'Renovation and improvement of the Matnog Municipal Building to enhance service delivery and provide better facilities for municipal employees and citizens.',
                'objectives' => 'Improve municipal building facilities. Enhance public service delivery.',
                'barangay_id' => $barangays->where('name', 'LIKE', '%Poblacion%')->first()?->id ?? $barangays->random()->id,
                'contractor_id' => $contractors->where('name', 'LIKE', '%Matnog Infrastructure%')->first()?->id ?? $contractors->random()->id,
                'budget_allocated' => 12000000.00,
                'amount_spent' => 9500000.00,
                'status' => 'In Progress',
                'start_date' => Carbon::now()->subMonths(8),
                'estimated_completion_date' => Carbon::now()->addMonths(4),
            ],
            [
                'title' => 'Road Improvement - Barangay Tabunan',
                'description' => 'Concrete paving and road improvement project in Barangay Tabunan (Poblacion), Matnog, Sorsogon.',
                'objectives' => 'Improve road conditions. Enhance transportation and accessibility.',
                'barangay_id' => $barangays->where('name', 'Tabunan (Poblacion)')->first()?->id ?? $barangays->random()->id,
                'contractor_id' => $contractors->where('name', 'LIKE', '%Bicol Builders%')->first()?->id ?? $contractors->random()->id,
                'budget_allocated' => 3500000.00,
                'amount_spent' => 2800000.00,
                'status' => 'In Progress',
                'start_date' => Carbon::now()->subMonths(2),
                'estimated_completion_date' => Carbon::now()->addMonths(2),
            ],
            [
                'title' => 'Drainage System - Barangay Gadgaron',
                'description' => 'Construction of drainage system in Barangay Gadgaron, Matnog, Sorsogon to improve water flow and prevent flooding.',
                'objectives' => 'Improve drainage system. Prevent flooding in the area.',
                'barangay_id' => $barangays->where('name', 'Gadgaron')->first()?->id ?? $barangays->random()->id,
                'contractor_id' => $contractors->where('name', 'LIKE', '%Flood Control%')->first()?->id ?? $contractors->random()->id,
                'budget_allocated' => 4200000.00,
                'amount_spent' => 4200000.00,
                'status' => 'Completed',
                'start_date' => Carbon::now()->subMonths(10),
                'estimated_completion_date' => Carbon::now()->subMonths(1),
                'actual_completion_date' => Carbon::now()->subMonths(1),
            ],
        ];

        foreach ($projects as $projectData) {
            $project = Project::create($projectData);

            // Attach municipal officials to project
            if ($officials->isNotEmpty()) {
                $projectOfficials = $officials->random(rand(2, 4));
                foreach ($projectOfficials as $official) {
                    $project->officials()->attach($official->id, [
                        'role_in_project' => $this->getRandomRole()
                    ]);
                }
            }

            // Create sample transactions for each project
            $this->createTransactionsForProject($project, $officials);
        }
    }

    private function getRandomRole()
    {
        $roles = ['Project Manager', 'Oversight Committee', 'Technical Adviser', 'Budget Officer', 'Quality Control'];
        return $roles[array_rand($roles)];
    }

    private function createTransactionsForProject($project, $officials)
    {
        $totalSpent = $project->amount_spent;
        $numTransactions = rand(3, 6);
        $amountPerTransaction = $totalSpent / $numTransactions;

        for ($i = 0; $i < $numTransactions; $i++) {
            $official = $officials->isNotEmpty() ? $officials->random() : null;
            
            Transaction::create([
                'project_id' => $project->id,
                'official_id' => $official?->id,
                'transaction_date' => Carbon::now()->subDays(rand(1, 90)),
                'description' => $this->getRandomTransactionDescription(),
                'amount' => round($amountPerTransaction + (rand(-10000, 10000)), 2),
            ]);
        }
    }

    private function getRandomTransactionDescription()
    {
        $descriptions = [
            'Payment for construction materials',
            'Labor cost payment',
            'Equipment rental',
            'Initial project mobilization',
            'Progress payment - Phase 1',
            'Progress payment - Phase 2',
            'Final payment and inspection',
            'Additional materials purchase',
        ];
        return $descriptions[array_rand($descriptions)];
    }
}

