<?php

namespace App\Controllers;

use App\Repositories\CustomerRepository;
use App\Repositories\MotorRepository;
use App\Repositories\RepairRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class StatisticsController
{
    /**
     * Γενικά στατιστικά - συνολικά numbers
     */
    public static function getOverviewStats(Request $request, Response $response): Response
    {
        try {
            $customerRepository = new CustomerRepository();
            $motorRepository = new MotorRepository();
            $repairRepository = new RepairRepository();

            $stats = [
                'totalCustomers' => $customerRepository->getTotalCount(),
                'totalMotors' => $motorRepository->getTotalCount(),
                'totalRepairs' => $repairRepository->getTotalCount(),
                'individualCustomers' => $customerRepository->getCountByType('individual'),
                'factoryCustomers' => $customerRepository->getCountByType('factory'),
                'currentMonthMotors' => $motorRepository->getCountByMonth(date('Y-m')),
                'currentMonthRepairs' => $repairRepository->getCountByMonth(date('Y-m')),
                'currentMonthRevenue' => $repairRepository->getRevenueByMonth(date('Y-m')),
                'yearlyRevenue' => $repairRepository->getRevenueByYear(date('Y')),
                 // Monthly trends για charts
                'monthlyCustomerTrends' => $customerRepository->getMonthlyTrends(),
                'monthlyMotorTrends' => $motorRepository->getMonthlyTrends(),
                'monthlyRepairTrends' => $repairRepository->getMonthlyTrends(),
                'monthlyRevenueTrends' => $repairRepository->getMonthlyRevenueTrends()
            ];

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $stats
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error fetching overview statistics: ' . $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Comprehensive dashboard data - όλα τα στατιστικά μαζί
     */
    public static function getDashboardData(Request $request, Response $response): Response
    {
        try {
            $customerRepository = new CustomerRepository();
            $motorRepository = new MotorRepository();
            $repairRepository = new RepairRepository();

            $currentMonth = date('Y-m');
            $currentYear = date('Y');
            
            $dashboardData = [
                'overview' => [
                    'totalCustomers' => $customerRepository->getTotalCount(),
                    'totalMotors' => $motorRepository->getTotalCount(),
                    'totalRepairs' => $repairRepository->getTotalCount(),
                    'currentMonthRevenue' => $repairRepository->getRevenueByMonth($currentMonth),
                ],
                'customerTypes' => [
                    'individual' => $customerRepository->getCountByType('individual'),
                    'factory' => $customerRepository->getCountByType('factory')
                ],
                'monthlyTrends' => self::getMonthlyTrendsData($currentYear, $motorRepository, $repairRepository),
                'topBrands' => $motorRepository->getTopBrands(5),
                'repairStatus' => $repairRepository->getCountByStatus()
            ];

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $dashboardData
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Error fetching dashboard data: ' . $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    private static function getMonthlyTrendsData($year, $motorRepository, $repairRepository)
    {
        $trends = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthKey = sprintf('%s-%02d', $year, $month);
            $trends[] = [
                'month' => $month,
                'motors' => $motorRepository->getCountByMonth($monthKey),
                'repairs' => $repairRepository->getCountByMonth($monthKey),
                'revenue' => $repairRepository->getRevenueByMonth($monthKey)
            ];
        }
        return $trends;
    }
}