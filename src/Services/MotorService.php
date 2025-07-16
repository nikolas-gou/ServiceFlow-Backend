<?php

namespace App\Services;

use App\Repositories\MotorRepository;

class MotorService 
{
    private $motorRepository;

    public function __construct(MotorRepository $motorRepository) {
        $this->motorRepository = $motorRepository;
    }


    // get motor stats for dashboard and motor page(in future)
    public function getMotorStats(): array
    {
        return [
            'totalMotors' => $this->motorRepository->getTotalCountFiltered(),
            'motorTypes' => [
                'totalOnePhaseMotors' => $this->motorRepository->getTotalCountFiltered(['type_of_volt' => '1-phase']),
                'totalThreePhaseMotors' => $this->motorRepository->getTotalCountFiltered(['type_of_volt' => '3-phase']),
                'totalElMotorMotors' => $this->motorRepository->getTotalCountFiltered(['type_of_motor' => 'el_motor']),
                'totalPumpMotors' => $this->motorRepository->getTotalCountFiltered(['type_of_motor' => 'pump']),
                'totalGeneratorMotors' => $this->motorRepository->getTotalCountFiltered(['type_of_motor' => 'generator']),
            ],
            'topBrands' => $this->motorRepository->getTopBrands(5),
            'trends' => [ 
                'monthlyTrends' => $this->motorRepository->getMonthlyTrendsFiltered(),
                'monthlyOnePhaseTrends' => $this->motorRepository->getMonthlyTrendsFiltered(['type_of_volt' => '1-phase']),
                'monthlyThreePhaseTrends' => $this->motorRepository->getMonthlyTrendsFiltered(['type_of_volt' => '3-phase']),
                'monthlyElMotorTrends' => $this->motorRepository->getMonthlyTrendsFiltered(['type_of_motor' => 'el_motor']),
                'monthlyPumpTrends' => $this->motorRepository->getMonthlyTrendsFiltered(['type_of_motor' => 'pump']),
                'monthlyGeneratorTrends' => $this->motorRepository->getMonthlyTrendsFiltered(['type_of_motor' => 'generator']),
            ]
        ];
    }

    /**
     * Connectionism statistics for connectionism page
     */
    public function getConnectionism(): array
    {
        // Αρχικοποίηση με μηδενικές τιμές
        $stats = [
            'totalSimple' => 0,
            'totalOneTimeParallel' => 0,
            'totalTwoTimesParallel' => 0,
            'totalThreeTimesParallel' => 0,
            'totalOther' => 0,
            'monthlySimpleTrends' => [],
            'monthlyOneTimeParallelTrends' => [],
            'monthlyTwoTimesParallelTrends' => [],
            'monthlyThreeTimesParallelTrends' => [],
            'monthlyOtherTrends' => []
        ];

        // Λήψη συνολικών counts
        $connectionismCounts = $this->motorRepository->getConnectionismCounts();
        
        // Ανάθεση counts στο κατάλληλο πεδίο
        foreach ($connectionismCounts as $row) {
            $connectionism = $row['connectionism'];
            $count = (int) $row['count'];
            
            switch ($connectionism) {
                case 'simple':
                    $stats['totalSimple'] = $count;
                    break;
                case '1-parallel':
                    $stats['totalOneTimeParallel'] = $count;
                    break;
                case '2-parallel':
                    $stats['totalTwoTimesParallel'] = $count;
                    break;
                case '3-parallel':
                    $stats['totalThreeTimesParallel'] = $count;
                    break;
                case 'other':
                    $stats['totalOther'] = $count;
                    break;
            }
        }

        // Λήψη μηνιαίων δεδομένων
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        $connectionismTypes = ['simple', '1-parallel', '2-parallel', '3-parallel', 'other'];
        
        foreach ($connectionismTypes as $type) {
            $monthlyData = $this->motorRepository->getMonthlyConnectionismData($type, $currentYear, $currentMonth);
            
            // Δημιουργία array με όλους τους μήνες
            $monthlyArray = [];
            for ($month = 1; $month <= $currentMonth; $month++) {
                $monthlyArray[] = 0;
            }
            
            // Συμπλήρωση με πραγματικά δεδομένα
            foreach ($monthlyData as $row) {
                $monthIndex = (int)$row['month'] - 1;
                $monthlyArray[$monthIndex] = (int)$row['count'];
            }
            
            // Ανάθεση στο κατάλληλο πεδίο
            switch ($type) {
                case 'simple':
                    $stats['monthlySimpleTrends'] = $monthlyArray;
                    break;
                case '1-parallel':
                    $stats['monthlyOneTimeParallelTrends'] = $monthlyArray;
                    break;
                case '2-parallel':
                    $stats['monthlyTwoTimesParallelTrends'] = $monthlyArray;
                    break;
                case '3-parallel':
                    $stats['monthlyThreeTimesParallelTrends'] = $monthlyArray;
                    break;
                case 'other':
                    $stats['monthlyOtherTrends'] = $monthlyArray;
                    break;
            }
        };

        return $stats;
    }
}