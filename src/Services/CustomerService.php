<?php

namespace App\Services;

use App\Repositories\CustomerRepository;
use App\Helpers\ServiceHelper;

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
        $result = [];

        // Σύνολο πελατών
        $result['totalCustomers'] = ServiceHelper::safeField(
            fn() => $this->customerRepository->getTotalCount(),
            'Σφάλμα στο σύνολο πελατών'
        );
    
        // Τύποι πελατών
        $result['customerTypes'] = [
            'individual' => ServiceHelper::safeField(
                fn() => $this->customerRepository->getCountByType('individual'),
                'Σφάλμα στους ιδιώτες'
            ),
            'factory' => ServiceHelper::safeField(
                fn() => $this->customerRepository->getCountByType('factory'),
                'Σφάλμα στα εργοστάσια'
            )
        ];
    
        // Trends
        $result['trends'] = [
            'monthlyTrends' => ServiceHelper::safeField(
                fn() => $this->customerRepository->getMonthlyTrends(),
                'Σφάλμα στα μηνιαία trends'
            ),
            'monthlyIndividualTrends' => ServiceHelper::safeField(
                fn() => $this->customerRepository->getMonthlyTrendsByType("individual"),
                'Σφάλμα στα trends ιδιωτών'
            ),
            'monthlyFactoryTrends' => ServiceHelper::safeField(
                fn() => $this->customerRepository->getMonthlyTrendsByType("factory"),
                'Σφάλμα στα trends εργοστασίων'
            )
        ];
    
        // Top πελάτες
        $result['topCustomersByRevenue'] = ServiceHelper::safeField(
            fn() => $this->customerRepository->getTopCustomerByRevenue(5),
            'Σφάλμα στους top πελάτες'
        );
    
        return $result;
    }
}
