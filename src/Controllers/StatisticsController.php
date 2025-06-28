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
     * Στατιστικά πελατών - ολοκληρωμένα δεδομένα
     */
    public function getCustomerStats(Request $request, Response $response): Response
    {
        try {
            $customerStats = [
                // Βασικά στατιστικά
                'totalCustomers' => $this->customerRepository->getTotalCount(),
                'monthlyTrends' => $this->customerRepository->getMonthlyTrends(),
                
                // Στατιστικά ανά κατηγορία
                'customerTypes' => [
                    'individual' => $this->customerRepository->getCountByType('individual'),
                    'factory' => $this->customerRepository->getCountByType('factory')
                ],
                
                // Λεπτομερή στατιστικά ανά κατηγορία
                'typeStats' => $this->customerRepository->getCustomerTypeStats(),
                
                // Πελάτες ανά μήνα ανά κατηγορία
                'customersByTypeAndMonth' => $this->customerRepository->getCustomersByTypeAndMonth(),
                
                // Λεπτομερή μηνιαία στατιστικά
                'detailedMonthlyStats' => $this->customerRepository->getDetailedMonthlyStats(),
                
                // Καλύτεροι πελάτες με βάση τα έσοδα
                'topCustomersByRevenue' => $this->customerRepository->getTopCustomerByRevenue(10),
                
                // Τρέχοντος μήνα στατιστικά
                'currentMonthStats' => [
                    'total' => $this->customerRepository->getCountByMonth(date('Y-m')),
                    'individual' => $this->customerRepository->getCountByTypeAndMonth('individual', date('Y-m')),
                    'factory' => $this->customerRepository->getCountByTypeAndMonth('factory', date('Y-m'))
                ]
            ];

            return ResponseHelper::success($response, $customerStats, 'Customer statistics retrieved successfully');

        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching customer statistics: ' . $e->getMessage());
        }
    }
}