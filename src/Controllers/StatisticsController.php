<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHelper;
use App\Services\DashboardService;
use App\Services\CustomerService;
use App\Services\MotorService;

class StatisticsController
{
    private $motorService;
    private $dashboardService;
    private $customerService;

    public function __construct(
        MotorService $motorService,
        DashboardService $dashboardService,
        CustomerService $customerService
    ) {
        $this->motorService = $motorService;
        $this->dashboardService = $dashboardService;
        $this->customerService = $customerService;
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
           $customerStats = $this->customerService->getCustomerStats();
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
           $connectionismStats = $this->motorService->getConnectionism();
            return ResponseHelper::success($response, $connectionismStats, 'Connectionism statistics retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Error fetching connectionism statistics: ' . $e->getMessage());
        }
    }
}