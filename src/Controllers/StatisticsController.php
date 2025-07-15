<?php

namespace App\Controllers;

use App\Repositories\CustomerRepository;
use App\Repositories\MotorRepository;
use App\Repositories\RepairRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHelper;
use App\Services\DashboardService;

class StatisticsController
{
    private $customerRepository;
    private $motorRepository;
    private $repairRepository;
    private $dashboardService;

    public function __construct(
        CustomerRepository $customerRepository,
        MotorRepository $motorRepository,
        RepairRepository $repairRepository,
        DashboardService $dashboardService
    ) {
        $this->customerRepository = $customerRepository;
        $this->motorRepository = $motorRepository;
        $this->repairRepository = $repairRepository;
        $this->dashboardService = $dashboardService;
    }


    /**
     * Comprehensive dashboard data - όλα τα στατιστικά μαζί
     */
    public function getDashboardData(Request $request, Response $response): Response
    {
        try {
            $dashboardData = $this->dashboardService->getDashboardData();
            return ResponseHelper::success($response, $dashboardData, 'Dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * Στατιστικά πελατών
     */
    public function getCustomerStats(Request $request, Response $response): Response
    {
        try {
           $customerStats = $this->dashboardService->getCustomerStats();
            return ResponseHelper::success($response, $customerStats, 'Customer statistics retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching customer statistics: ' . $e->getMessage());
        }
    }

    /**
     * Connectionism statistics
     */
    public function getConnectionism(Request $request, Response $response): Response
    {
        try {
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
            }

            return ResponseHelper::success($response, $stats, 'Connectionism statistics retrieved successfully');

        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching connectionism statistics: ' . $e->getMessage());
        }
    }
}