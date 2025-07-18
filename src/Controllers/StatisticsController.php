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
        $dashboardData = $this->dashboardService->getDashboardData();
        return ResponseHelper::success($response, $dashboardData, 'Dashboard data retrieved successfully');
    }

    /**
     * Στατιστικά πελατών
     */
    public function getCustomerStats(Request $request, Response $response): Response
    {
        $customerStats = $this->customerService->getCustomerStats();
        return ResponseHelper::success($response, $customerStats, 'Customer statistics retrieved successfully');
    }

    /**
     * Connectionism statistics
     */
    public function getConnectionism(Request $request, Response $response): Response
    {
        $connectionismStats = $this->motorService->getConnectionism();
        return ResponseHelper::success($response, $connectionismStats, 'Connectionism statistics retrieved successfully');
    }
}