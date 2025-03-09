<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\RepairRepository;

class RepairController {
    private $repairRepository;

    public function __construct() {
        $this->repairRepository = new RepairRepository();
    }

    public function getAll(Request $request, Response $response) {
        $repairs = $this->repairRepository->getAll();
        $response->getBody()->write(json_encode($repairs, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getRepairById(Request $request, Response $response, $args) {
        $repair = $this->repairRepository->getRepairById($args['id']);
        $response->getBody()->write(json_encode($repair));
        return $response->withHeader('Content-Type', 'application/json');
    }
}