<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\CustomerRepository;
use App\Helpers\ResponseHelper;

class CustomerController
{
    private $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function getAll(Request $request, Response $response): Response
    {
        try {
            $customers = $this->customerRepository->getAll();
            return ResponseHelper::success($response, $customers, 'Customers retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Failed to retrieve customers: ' . $e->getMessage());
        }
    }

    public function getCustomerById(Request $request, Response $response, $args): Response
    {
        try {
            $customer = $this->customerRepository->getCustomerById($args['id']);
            
            if (!$customer) {
                return ResponseHelper::notFound($response, 'Customer not found');
            }
            
            return ResponseHelper::success($response, $customer, 'Customer retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Failed to retrieve customer: ' . $e->getMessage());
        }
    }

    public function createCustomer(Request $request, Response $response): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);
            
            if (!$data) {
                return ResponseHelper::validationError($response, ['Invalid JSON data']);
            }
            
            $customer = new \App\Models\Customer($data);
            
            if (!$customer->isValid()) {
                return ResponseHelper::validationError($response, ['Customer data is invalid']);
            }
            
            $customerId = $this->customerRepository->createCustomer($customer);
            
            if ($customerId) {
                $customer->id = $customerId;
                return ResponseHelper::success($response, $customer, 'Customer created successfully', 201);
            }
            
            return ResponseHelper::serverError($response, 'Failed to create customer');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Failed to create customer: ' . $e->getMessage());
        }
    }
}
