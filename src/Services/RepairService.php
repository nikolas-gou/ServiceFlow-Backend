<?php

namespace App\Services;

use App\Repositories\RepairRepository;

class RepairService 
{
    private $repairRepository;

    public function __construct(RepairRepository $repairRepository)
    {
        $this->repairRepository = $repairRepository;
    }

    public function getRepairStats(): array 
    {
        return [
            'totalRepairs' => $this->repairRepository->getTotalCount(),
            'trends' => [
                 'monthlyTrends' => $this->repairRepository->getMonthlyTrends(),
            ], 
        ];
    }

    public function getRevenueStats(): array 
    {
        return [
            'yearlyRevenue' => $this->repairRepository->getRevenueByYear(date('Y')),
            'trends' => [
                'monthlyTrends' => $this->repairRepository->getMonthlyRevenueTrends()
            ]
        ];
    }
}