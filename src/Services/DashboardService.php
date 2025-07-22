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
     * Επιστρέφει τα ονόματα των μηνών στα ελληνικά
     */
    private function getGreekMonths(): array
    {
        return [
            'Ιαν',
            'Φεβ',
            'Μαρ',
            'Απρ',
            'Μάι',
            'Ιουν',
            'Ιουλ',
            'Αυγ',
            'Σεπ',
            'Οκτ',
            'Νοε',
            'Δεκ',
        ];
    }

    /**
     * Επιστρέφει τα μηνιαία δεδομένα για charts
     */
    private function getMonthlyChartData(): array
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        
        // Επιστρέφουμε μόνο τους μήνες μέχρι τον τρέχοντα
        $months = array_slice($this->getGreekMonths(), 0, $currentMonth);
        
        return [
            'labels' => $months,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'totalMonths' => $currentMonth
        ];
    }

    /**
     * Comprehensive dashboard data - όλα τα στατιστικά μαζί
     */
    public function getDashboardData(): array
    {
        $result = [];
        
        // Προσθήκη μηνιαίων δεδομένων για charts
        $result['chartData'] = $this->getMonthlyChartData();
        
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
