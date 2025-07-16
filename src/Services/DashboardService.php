<?php

namespace App\Services;

use App\Services\RepairService;
use App\Services\CustomerService;
use App\Services\MotorService;

class DashboardService
{
    private $motorService;
    private $repairService;
    private $customerService;

    public function __construct(
        MotorService $motorService,
        RepairService $repairService,
        CustomerService $customerService
    ) {
        $this->motorService = $motorService;
        $this->repairService = $repairService;
        $this->customerService = $customerService;
    }

    /**
     * Comprehensive dashboard data - όλα τα στατιστικά μαζί
     */
    public function getDashboardData(): array
    {
        return [
            'customer' => $this->customerService->getCustomerStats(),
            'motor' => $this->motorService->getMotorStats(),
            'repair' => $this->repairService->getRepairStats(),
            'revenue' => $this->repairService->getRevenueStats(),
        ];
    }
}
