<?php

namespace App\Services;

use App\Helpers\ServiceHelper;
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
        $result = [];
        $result['customer'] = ServiceHelper::safeField(
            fn() => $this->customerService->getCustomerStats(),
            'Σφάλμα στα στατιστικά πελατών'
        );
        $result['motor'] = ServiceHelper::safeField(
            fn() => $this->motorService->getMotorStats(),
            'Σφάλμα στα στατιστικά κινητήρων'
        );
        $result['repair'] = ServiceHelper::safeField(
            fn() => $this->repairService->getRepairStats(),
            'Σφάλμα στα στατιστικά επισκευών'
        );
        $result['revenue'] = ServiceHelper::safeField(
            fn() => $this->repairService->getRevenueStats(),
            'Σφάλμα στα στατιστικά εσόδων'
        );
        return $result;
    }
}
