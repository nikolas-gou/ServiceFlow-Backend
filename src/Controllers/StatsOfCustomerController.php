<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\StatsOfCustomerRepository;

class StatsOfCustomerController {
    private $statsOfCustomerRepository;

    public function __construct() {
        $this->statsOfCustomerRepository = new StatsOfCustomerRepository();
    }

    public function stats(Request $request, Response $response) {
        $stats = $this->statsOfCustomerRepository->getAllStats();
        $response->getBody()->write(json_encode($stats, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

}