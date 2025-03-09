<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\StatsOfRepairRepository;

class StatsOfRepairController {
    private $statsOfRepairRepository;

    public function __construct() {
        $this->statsOfRepairRepository = new StatsOfRepairRepository();
    }

    public function stats(Request $request, Response $response) {
        $stats = $this->statsOfRepairRepository->getTotalRepairs();
        $response->getBody()->write(json_encode($stats, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // in-progress **
    // public function getStats(Request $request, Response $response) {
    //     $totalRepairs = $this->statsOfRepairRepository->getTotalRepairs();
    //     $repairsPerMonth = $this->statsOfRepairRepository->getRepairsPerMonth();
    //     $totalRepairsByType = $this->statsOfRepairRepository->getTotalRepairsByType();
    //     $response->getBody()->write(json_encode([
    //         "totalRepairs" => $totalRepairs, 
    //         "repairsPerMonth" => $repairsPerMonth, 
    //         "totalRepairsByType" =>$totalRepairsByType
    //     ], JSON_UNESCAPED_UNICODE));
    //     return $response->withHeader('Content-Type', 'application/json');
    // }

}