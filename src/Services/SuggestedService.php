<?php

namespace App\Services;

use App\Services\MotorService;
use App\Services\CustomerService;

class SuggestedService
{
    private MotorService $motorService;
    private CustomerService $customerService;

    public function __construct(
        MotorService $motorService,
        CustomerService $customerService
    ) {
        $this->motorService = $motorService;
        $this->customerService = $customerService;
    }

    public function getSuggestedData(): array
    {
        $motor = $this->motorService->getSuggestedData();
        $customer = $this->customerService->getSuggestedData();

        $errors = $this->collectErrors([
            'motor.crossSection' => $motor['crossSection'] ?? null,
            'motor.step' => $motor['step'] ?? null,
            'motor.manufacturer' => $motor['manufacturer'] ?? null,
            'motor.description' => $motor['description'] ?? null,
            'customer' => $customer,
        ]);

        return [
            'motor' => $motor,
            'customer' => $customer,
            'meta' => [
                'hasErrors' => !empty($errors),
                'errors' => $errors,
            ],
        ];
    }

    private function collectErrors(array $entries): array
    {
        $errors = [];

        foreach ($entries as $scope => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $errorMessage = $entry['error'] ?? null;

            if ($errorMessage === null) {
                continue;
            }

            $errors[] = [
                'scope' => $scope,
                'message' => $errorMessage,
                'details' => $entry['details'] ?? null,
            ];
        }

        return $errors;
    }
}