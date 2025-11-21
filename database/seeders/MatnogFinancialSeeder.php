<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinancialRecord;
use App\Models\Barangay;
use App\Models\BarangayIraShare;

class MatnogFinancialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Financial records for Matnog Municipality
        $financialRecords = [
            [
                'year' => 2020,
                'total_revenue' => 160600000.00,
                'ira_allocation' => 144540000.00, // ~90% of total revenue (80-90% dependency)
                'service_business_income' => 12000000.00, // Estimated
                'local_tax_collections' => 4060000.00, // Remaining after IRA
                'property_tax' => 2000000.00, // Estimated
                'goods_services_tax' => 2060000.00, // Estimated
                'total_expenditures' => 163700000.00,
                'personnel_services' => 60000000.00, // Estimated based on 2021 ratio
                'maintenance_operating_expenses' => 90000000.00, // Estimated based on 2021 ratio
                'capital_outlay' => 13700000.00, // Calculated: 163.7M - 60M - 90M
                'fiscal_balance' => 4000000.00, // Surplus (160.6M - 163.7M = -3.1M, but stated as 4M surplus)
                'total_assets' => 195400000.00,
                'total_liabilities' => 98000000.00,
                'net_equity' => 97300000.00,
            ],
            [
                'year' => 2021,
                'total_revenue' => 174300000.00,
                'ira_allocation' => 153000000.00, // 87.7% of total revenue
                'service_business_income' => 17500000.00,
                'local_tax_collections' => 3600000.00,
                'property_tax' => 0, // Increased in 2021
                'goods_services_tax' => 0, // Decreased in 2021
                'total_expenditures' => 164300000.00,
                'personnel_services' => 62900000.00,
                'maintenance_operating_expenses' => 94000000.00,
                'capital_outlay' => 7400000.00, // Calculated: 164.3M - 62.9M - 94.0M
                'fiscal_balance' => 2360000.00, // Surplus (17.43M - 16.43M = 1.0M, but stated as 2.36M)
                'total_assets' => 234100000.00,
                'total_liabilities' => 91000000.00,
                'net_equity' => 143000000.00,
                'notes' => 'Revenue growth of ₱13.7 million from 2020. Asset growth of ₱38.7 million.',
            ],
            [
                'year' => 2022,
                'total_revenue' => 234500000.00,
                'ira_allocation' => 211050000.00, // ~90% of total revenue
                'service_business_income' => 18000000.00, // Estimated growth
                'local_tax_collections' => 5450000.00, // Estimated growth
                'property_tax' => 3500000.00, // Increased in 2021, continued growth
                'goods_services_tax' => 1950000.00, // Decreased trend
                'total_expenditures' => 220000000.00, // Estimated
                'personnel_services' => 70000000.00, // Estimated growth
                'maintenance_operating_expenses' => 130000000.00, // Estimated growth
                'capital_outlay' => 20000000.00, // Estimated
                'fiscal_balance' => 14500000.00, // Estimated surplus
                'total_assets' => 250000000.00, // Estimated growth
                'total_liabilities' => 85000000.00, // Estimated decrease
                'net_equity' => 165000000.00, // Estimated growth
                'notes' => 'Historical income trend shows growth from ₱65.7M in 2012 to ₱234.5M in 2022.',
            ],
        ];

        foreach ($financialRecords as $record) {
            // Remove total_revenue - it will be calculated automatically from components
            $recordData = $record;
            unset($recordData['total_revenue']);
            
            FinancialRecord::updateOrCreate(
                ['year' => $record['year']],
                $recordData
            );
        }

        // Barangay IRA Shares - Actual data from CY 2010 Computation
        // Total: ₱33,137,534.00
        $barangayIraShares = [
            'Balocawe' => 821855.00,
            'Banogao' => 685723.00,
            'Banuangdaan' => 706208.00,
            'Bariis' => 905781.00,
            'Bolo' => 946422.00,
            'Bon-Ot Big' => 899173.00,
            'Bon-Ot Small' => 654663.00,
            'Cabagahan' => 686714.00,
            'Calayuan' => 728677.00,
            'Calintaan' => 915693.00,
            'Caloocan (Poblacion)' => 878356.00,
            'Calpi' => 584615.00,
            'Camachiles (Poblacion)' => 1002263.00,
            'Camcaman (Poblacion)' => 837715.00,
            'Coron-coron' => 1019445.00,
            'Culasi' => 1100727.00, // Highest IRA
            'Gadgaron' => 1079581.00,
            'Genablan Occidental' => 717112.00,
            'Genablan Oriental' => 816238.00,
            'Hidhid' => 889260.00,
            'Laboy' => 798065.00,
            'Lajong' => 747841.00,
            'Mambajog' => 732642.00,
            'Manjumlad' => 900494.00,
            'Manurabi' => 788483.00,
            'Naburacan' => 667219.00,
            'Paghuliran' => 703565.00,
            'Pangi' => 737929.00,
            'Pawa' => 962613.00,
            'Poropandan' => 781874.00,
            'Santa Isabel' => 743876.00,
            'Sinalmacan' => 671515.00,
            'Sinang-Atan' => 642438.00,
            'Sinibaran' => 794430.00,
            'Sisigon' => 952370.00,
            'Sua' => 976490.00,
            'Sulangan' => 756432.00,
            'Tablac (Poblacion)' => 1004245.00,
            'Tabunan (Poblacion)' => 1096432.00,
            'Tugas' => 800000.00, // Estimated (missing in document)
        ];
        
        $barangays = Barangay::all();
        
        if ($barangays->isNotEmpty()) {
            foreach ($barangays as $barangay) {
                // Find matching IRA share by barangay name (exact match or partial)
                $iraShare = null;
                
                // Try exact match first
                if (isset($barangayIraShares[$barangay->name])) {
                    $iraShare = $barangayIraShares[$barangay->name];
                } else {
                    // Try matching without (Poblacion) suffix
                    $nameWithoutPoblacion = str_replace(' (Poblacion)', '', $barangay->name);
                    if (isset($barangayIraShares[$nameWithoutPoblacion])) {
                        $iraShare = $barangayIraShares[$nameWithoutPoblacion];
                    } else {
                        // Try partial match
                        foreach ($barangayIraShares as $key => $value) {
                            if (stripos($barangay->name, $key) !== false || stripos($key, $barangay->name) !== false) {
                                $iraShare = $value;
                                break;
                            }
                        }
                    }
                }
                
                if ($iraShare) {
                    // Create or update IRA share for 2010 (the year of the computation)
                    BarangayIraShare::updateOrCreate(
                        [
                            'barangay_id' => $barangay->id,
                            'year' => 2010,
                        ],
                        [
                            'ira_share' => $iraShare,
                            'notes' => 'CY 2010 Computation - Based on population and equity distribution',
                        ]
                    );
                    
                    // Also create for 2021 (scaled up proportionally for current year)
                    // Scale factor: 2021 total would be higher, so we'll use a multiplier
                    // Assuming 2021 total barangay IRA is around 20% of municipal IRA (₱30.6M)
                    $scaleFactor = 30600000.00 / 33137534.00; // Scale to 2021 total
                    $iraShare2021 = $iraShare * $scaleFactor;
                    
                    BarangayIraShare::updateOrCreate(
                        [
                            'barangay_id' => $barangay->id,
                            'year' => 2021,
                        ],
                        [
                            'ira_share' => $iraShare2021,
                            'notes' => 'Scaled from 2010 computation based on 2021 municipal IRA allocation',
                        ]
                    );
                } else {
                    // If no match found, calculate based on population (fallback)
                    $totalPopulation = $barangays->sum('population');
                    $avgIraPerPerson = 33137534.00 / $totalPopulation;
                    $estimatedIra = $barangay->population * $avgIraPerPerson;
                    
                    BarangayIraShare::updateOrCreate(
                        [
                            'barangay_id' => $barangay->id,
                            'year' => 2010,
                        ],
                        [
                            'ira_share' => $estimatedIra,
                            'notes' => "Estimated based on population: {$barangay->population} (exact data not found)",
                        ]
                    );
                }
            }
        }
    }
}

