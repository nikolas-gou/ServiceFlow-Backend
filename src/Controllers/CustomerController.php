<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\CustomerRepository;

class CustomerController {
    private $customerRepository;

    public function __construct() {
        $this->customerRepository = new CustomerRepository();
    }

    public function getAll(Request $request, Response $response) {
        $customers = $this->customerRepository->getAll();
        $response->getBody()->write(json_encode($customers, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getCustomerById(Request $request, Response $response, $args) {
        $customer = $this->customerRepository->getCustomerById($args['id']);
        $response->getBody()->write(json_encode($customer));
        return $response->withHeader('Content-Type', 'application/json');
    }
}