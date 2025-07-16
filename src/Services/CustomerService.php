<?php

namespace App\Services;

use App\Repositories\CustomerRepository;

class CustomerService
{
    private $customerRepository;

    public function __construct(
        CustomerRepository $customerRepository,
    ) {
        $this->customerRepository = $customerRepository;
    }


    // statistics
    public function getCustomerStats(): array 
    {
        return [
            'totalCustomers' => $this->customerRepository->getTotalCount(),
            'customerTypes' => [
                'individual' => $this->customerRepository->getCountByType('individual'),
                'factory' => $this->customerRepository->getCountByType('factory')
            ],
            'trends' => [
                'monthlyTrends' => $this->customerRepository->getMonthlyTrends(),
                'monthlyIndividualTrends' => $this->customerRepository->getMonthlyTrendsByType("individual"),
                'monthlyFactoryTrends' => $this->customerRepository->getMonthlyTrendsByType("factory"),
            ],
            'topCustomersByRevenue' => $this->customerRepository->getTopCustomerByRevenue(5),
        ];
    }
}
