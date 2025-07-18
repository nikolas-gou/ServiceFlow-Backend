<?php

namespace App\Services;

use App\Repositories\RepairRepository;
use App\Helpers\ServiceHelper;

class RepairService 
{
    private $repairRepository;

    public function __construct(RepairRepository $repairRepository)
    {
        $this->repairRepository = $repairRepository;
    }

    public function getRepairStats(): array 
    {
        $result = [];
        $result['totalRepairs'] = ServiceHelper::safeField(
            fn() => $this->repairRepository->getTotalCount(),
            'Σφάλμα στο σύνολο επισκευών'
        );
        $result['trends'] = [
            'monthlyTrends' => ServiceHelper::safeField(
                fn() => $this->repairRepository->getMonthlyTrends(),
                'Σφάλμα στα μηνιαία trends επισκευών'
            )
        ];
        return $result;
    }

    public function getRevenueStats(): array 
    {
        $result = [];
        $result['yearlyRevenue'] = ServiceHelper::safeField(
            fn() => $this->repairRepository->getRevenueByYear(date('Y')),
            'Σφάλμα στα ετήσια έσοδα'
        );
        $result['trends'] = [
            'monthlyTrends' => ServiceHelper::safeField(
                fn() => $this->repairRepository->getMonthlyRevenueTrends(),
                'Σφάλμα στα μηνιαία έσοδα'
            )
        ];
        return $result;
    }
}