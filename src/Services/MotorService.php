<?php

namespace App\Services;

use App\Repositories\MotorRepository;
use App\Repositories\MotorCrossSectionLinksRepository;
use App\Helpers\ServiceHelper;

class MotorService 
{
    private MotorRepository $motorRepository;
    private MotorCrossSectionLinksRepository $motorCrossSectionLinksRepository;

    public function __construct(
        MotorRepository $motorRepository, 
        MotorCrossSectionLinksRepository $motorCrossSectionLinksRepository
    ) {
        $this->motorRepository = $motorRepository;
        $this->motorCrossSectionLinksRepository = $motorCrossSectionLinksRepository;
    }

    // get motor stats for dashboard and motor page(in future)
    public function getMotorStats(): array
    {
        $result = [];

        // Total motors
        $result['totalMotors'] = ServiceHelper::safeField(
            fn() => $this->motorRepository->getTotalCountFiltered(),
            'Σφάλμα στο σύνολο κινητήρων'
        );

        // Motor types
        $result['motorTypes'] = [
            'totalOnePhaseMotors' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getTotalCountFiltered(['type_of_volt' => '1-phase']),
                'Σφάλμα στους μονοφασικούς κινητήρες'
            ),
            'totalThreePhaseMotors' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getTotalCountFiltered(['type_of_volt' => '3-phase']),
                'Σφάλμα στους τριφασικούς κινητήρες'
            ),
            'totalElMotorMotors' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getTotalCountFiltered(['type_of_motor' => 'el_motor']),
                'Σφάλμα στους ηλεκτρικούς κινητήρες'
            ),
            'totalPumpMotors' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getTotalCountFiltered(['type_of_motor' => 'pump']),
                'Σφάλμα στους αντλητικούς κινητήρες'
            ),
            'totalGeneratorMotors' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getTotalCountFiltered(['type_of_motor' => 'generator']),
                'Σφάλμα στους γεννήτριες'
            )
        ];

        // stepTypes
        $result['stepTypes'] = [
            'totalStandardStep' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getTotalCountFiltered(['type_of_step' => 'standard']),
                'Σφάλμα στους standard steps'
            ),
            'totalHalfStep' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getTotalCountFiltered(['type_of_step' => 'half']),
                'Σφάλμα στους half steps'
            ),
            'totalCombinedStep' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getTotalCountFiltered(['type_of_step' => 'combined']),
                'Σφάλμα στους combined steps'
            )
        ];

        // Top brands
        $result['topBrands'] = ServiceHelper::safeField(
            fn() => $this->motorRepository->getTopBrands(5),
            'Σφάλμα στα top brands'
        );

        // Trends
        $result['trends'] = [
            'monthlyTrends' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getMonthlyTrendsFiltered(),
                'Σφάλμα στα μηνιαία trends'
            ),
            'monthlyOnePhaseTrends' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getMonthlyTrendsFiltered(['type_of_volt' => '1-phase']),
                'Σφάλμα στα trends μονοφασικών'
            ),
            'monthlyThreePhaseTrends' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getMonthlyTrendsFiltered(['type_of_volt' => '3-phase']),
                'Σφάλμα στα trends τριφασικών'
            ),
            'monthlyElMotorTrends' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getMonthlyTrendsFiltered(['type_of_motor' => 'el_motor']),
                'Σφάλμα στα trends κινητήρων'
            ),
            'monthlyPumpTrends' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getMonthlyTrendsFiltered(['type_of_motor' => 'pump']),
                'Σφάλμα στα trends αντλιών'
            ),
            'monthlyGeneratorTrends' => ServiceHelper::safeField(
                fn() => $this->motorRepository->getMonthlyTrendsFiltered(['type_of_motor' => 'generator']),
                'Σφάλμα στα trends γεννητριών'
            )
        ];

        return $result;
    }

    /**
     * Connectionism statistics for connectionism page
     */
    public function getConnectionism(): array
    {
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

        // Connectionism counts
        try {
            $connectionismCounts = $this->motorRepository->getConnectionismCounts();
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
        } catch (\Throwable $e) {
            $stats['connectionismCountsError'] = [
                'error' => 'Σφάλμα στα connectionism counts',
                'details' => $e->getMessage()
            ];
        }

        // Monthly data
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        $connectionismTypes = ['simple', '1-parallel', '2-parallel', '3-parallel', 'other'];
        foreach ($connectionismTypes as $type) {
            try {
                $monthlyData = $this->motorRepository->getMonthlyConnectionismData($type, $currentYear, $currentMonth);
                $monthlyArray = array_fill(0, $currentMonth, 0);
                foreach ($monthlyData as $row) {
                    $monthIndex = (int)$row['month'] - 1;
                    $monthlyArray[$monthIndex] = (int)$row['count'];
                }
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
            } catch (\Throwable $e) {
                $errorKey = match($type) {
                    'simple' => 'monthlySimpleTrends',
                    '1-parallel' => 'monthlyOneTimeParallelTrends',
                    '2-parallel' => 'monthlyTwoTimesParallelTrends',
                    '3-parallel' => 'monthlyThreeTimesParallelTrends',
                    'other' => 'monthlyOtherTrends',
                };
                $stats[$errorKey] = [
                    'error' => 'Σφάλμα στα trends ' . $type,
                    'details' => $e->getMessage()
                ];
            }
        }
        return $stats;
    }

    public function getSuggestedData(): array
    {
        return [
            'crossSection' => ServiceHelper::formatSuggestedList(
                fn() => $this->motorCrossSectionLinksRepository->getSuggested(),
                'Αποτυχία ανάκτησης προτεινόμενων διατομών'
            ),
            'step' => ServiceHelper::formatSuggestedList(
                fn() => $this->motorRepository->getSuggestedSteps(),
                'Αποτυχία ανάκτησης προτεινόμενων βημάτων'
            ),
            'manufacturer' => ServiceHelper::formatSuggestedList(
                fn() => $this->motorRepository->getSuggestedManufacturers(),
                'Αποτυχία ανάκτησης προτεινόμενων μαρκών'
            ),
            'description' => ServiceHelper::formatSuggestedList(
                fn() => $this->motorRepository->getSuggestedDescriptions(),
                'Αποτυχία ανάκτησης προτεινόμενων περιγραφών κινητήρων'
            ),
        ];
    }

}