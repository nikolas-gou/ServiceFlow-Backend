<?php

namespace App\Controllers;

use App\Repositories\CustomerRepository;
use App\Repositories\MotorRepository;
use App\Repositories\RepairRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHelper;

class StatisticsController
{
    private $customerRepository;
    private $motorRepository;
    private $repairRepository;

    public function __construct(
        CustomerRepository $customerRepository,
        MotorRepository $motorRepository,
        RepairRepository $repairRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->motorRepository = $motorRepository;
        $this->repairRepository = $repairRepository;
    }

    /**
     * Γενικά στατιστικά - συνολικά numbers
     */
    public function getOverviewStats(Request $request, Response $response): Response
    {
        try {
            $stats = [
                'totalCustomers' => $this->customerRepository->getTotalCount(),
                'totalMotors' => $this->motorRepository->getTotalCount(),
                'totalRepairs' => $this->repairRepository->getTotalCount(),
                'individualCustomers' => $this->customerRepository->getCountByType('individual'),
                'factoryCustomers' => $this->customerRepository->getCountByType('factory'),
                'currentMonthMotors' => $this->motorRepository->getCountByMonth(date('Y-m')),
                'currentMonthRepairs' => $this->repairRepository->getCountByMonth(date('Y-m')),
                'currentMonthRevenue' => $this->repairRepository->getRevenueByMonth(date('Y-m')),
                'yearlyRevenue' => $this->repairRepository->getRevenueByYear(date('Y')),
                 // Monthly trends για charts
                'monthlyCustomerTrends' => $this->customerRepository->getMonthlyTrends(),
                'monthlyMotorTrends' => $this->motorRepository->getMonthlyTrends(),
                'monthlyRepairTrends' => $this->repairRepository->getMonthlyTrends(),
                'monthlyRevenueTrends' => $this->repairRepository->getMonthlyRevenueTrends()
            ];

            return ResponseHelper::success($response, $stats, 'Overview statistics retrieved successfully');

        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching overview statistics: ' . $e->getMessage());
        }
    }

    /**
     * Comprehensive dashboard data - όλα τα στατιστικά μαζί
     */
    public function getDashboardData(Request $request, Response $response): Response
    {
        try {
            $currentMonth = date('Y-m');
            $currentYear = date('Y');
            
            $dashboardData = [
                'overview' => [
                    'totalCustomers' => $this->customerRepository->getTotalCount(),
                    'totalMotors' => $this->motorRepository->getTotalCount(),
                    'totalRepairs' => $this->repairRepository->getTotalCount(),
                    'currentMonthRevenue' => $this->repairRepository->getRevenueByMonth($currentMonth),
                ],
                'customerTypes' => [
                    'individual' => $this->customerRepository->getCountByType('individual'),
                    'factory' => $this->customerRepository->getCountByType('factory')
                ],
                'monthlyTrends' => $this->getMonthlyTrendsData($currentYear),
                'topBrands' => $this->motorRepository->getTopBrands(5),
                'repairStatus' => $this->repairRepository->getCountByStatus()
            ];

            return ResponseHelper::success($response, $dashboardData, 'Dashboard data retrieved successfully');

        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching dashboard data: ' . $e->getMessage());
        }
    }

    private function getMonthlyTrendsData($year)
    {
        $trends = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthKey = sprintf('%s-%02d', $year, $month);
            $trends[] = [
                'month' => $month,
                'motors' => $this->motorRepository->getCountByMonth($monthKey),
                'repairs' => $this->repairRepository->getCountByMonth($monthKey),
                'revenue' => $this->repairRepository->getRevenueByMonth($monthKey)
            ];
        }
        return $trends;
    }

    /**
     * Στατιστικά πελατών
     */
    public function getCustomerStats(Request $request, Response $response): Response
    {
        try {
            $stats = [
                'totalCustomers' => $this->customerRepository->getTotalCount(),
                'customerTypes' => [
                    'individual' => $this->customerRepository->getCountByType('individual'),
                    'factory' => $this->customerRepository->getCountByType('factory')
                ],
                'monthlyTrends' => $this->customerRepository->getMonthlyTrends(),
                'customersByTypeAndMonth' => $this->customerRepository->getCustomersByTypeAndMonth(),
                'topCustomersByRevenue' => $this->customerRepository->getTopCustomerByRevenue(5),
                'typeStats' => $this->customerRepository->getCustomerTypeStats()
            ];

            return ResponseHelper::success($response, $stats, 'Customer statistics retrieved successfully');

        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching customer statistics: ' . $e->getMessage());
        }
    }

    /**
     * Στατιστικά επισκευών
     */
    public function getRepairStats(Request $request, Response $response): Response
    {
        try {
            $stats = [
                'totalCount' => $this->repairRepository->getTotalCount(),
                'monthlyTrends' => $this->repairRepository->getMonthlyTrends(),
                'byStatus' => $this->repairRepository->getCountByStatus(),
                'monthlyRevenue' => $this->repairRepository->getMonthlyRevenueTrends(),
                'yearlyRevenue' => $this->repairRepository->getRevenueByYear(date('Y')),
                'currentMonthRevenue' => $this->repairRepository->getRevenueByMonth(date('Y-m'))
            ];

            return ResponseHelper::success($response, $stats, 'Repair statistics retrieved successfully');

        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching repair statistics: ' . $e->getMessage());
        }
    }

    /**
     * Στατιστικά μοτέρ
     */
    public function getMotorStats(Request $request, Response $response): Response
    {
        try {
            $stats = [
                'totalCount' => $this->motorRepository->getTotalCount(),
                'monthlyTrends' => $this->motorRepository->getMonthlyTrends(),
                'topBrands' => $this->motorRepository->getTopBrands(10),
                'allBrands' => $this->motorRepository->getAllBrands()
            ];

            return ResponseHelper::success($response, $stats, 'Motor statistics retrieved successfully');

        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching motor statistics: ' . $e->getMessage());
        }
    }

    /**
     * Top brands statistics
     */
    public function getTopBrands(Request $request, Response $response): Response
    {
        try {
            $limit = (int) ($request->getQueryParams()['limit'] ?? 10);
            $brands = $this->motorRepository->getTopBrands($limit);

            return ResponseHelper::success($response, $brands, 'Top brands retrieved successfully');

        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching top brands: ' . $e->getMessage());
        }
    }

    /**
     * Revenue statistics
     */
    public function getRevenue(Request $request, Response $response): Response
    {
        try {
            $year = $request->getQueryParams()['year'] ?? date('Y');
            
            $revenue = [
                'yearly' => $this->repairRepository->getRevenueByYear($year),
                'monthly' => $this->repairRepository->getMonthlyRevenueTrends(),
                'currentMonth' => $this->repairRepository->getRevenueByMonth(date('Y-m'))
            ];

            return ResponseHelper::success($response, $revenue, 'Revenue statistics retrieved successfully');

        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching revenue statistics: ' . $e->getMessage());
        }
    }

    /**
     * Repair status statistics
     */
    public function getRepairStatus(Request $request, Response $response): Response
    {
        try {
            $statusStats = $this->repairRepository->getCountByStatus();

            return ResponseHelper::success($response, $statusStats, 'Repair status statistics retrieved successfully');

        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching repair status statistics: ' . $e->getMessage());
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