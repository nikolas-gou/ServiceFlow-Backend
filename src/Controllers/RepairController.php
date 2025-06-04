<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\RepairRepository;

class RepairController
{
    private $repairRepository;

    public function __construct()
    {
        $this->repairRepository = new RepairRepository();
    }

    public function getAll(Request $request, Response $response)
    {
        $repairs = $this->repairRepository->getAll();
        $response->getBody()->write(json_encode($repairs, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getRepairById(Request $request, Response $response, $args)
    {
        $repair = $this->repairRepository->getRepairById($args['id']);
        $response->getBody()->write(json_encode($repair));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function createRepair(Request $request, Response $response)
    {
        try {
            // Λάβετε το περιεχόμενο του αιτήματος
            $body = $request->getBody()->getContents();

            // Αποκωδικοποιήστε το JSON string σε αντικείμενο PHP
            $data = json_decode($body, true);

            // Έλεγχος για απαραίτητα δεδομένα
            if (!isset($data['repair']) || !isset($data['customer']) || !isset($data['motor'])) {
                $errorResponse = [
                    'status' => 'error',
                    'message' => 'Λείπουν απαραίτητα δεδομένα (repair, customer ή motor)'
                ];
                $response->getBody()->write(json_encode($errorResponse, JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // Αποθήκευση της επισκευής
            $newRepair = $this->repairRepository->createNewRepair(
                $data['repair'],
                $data['customer'],
                $data['motor'],
                $data["common_faults"]
            );

            // Επιτυχής απάντηση
            $successResponse = [
                'status' => 'success',
                'message' => 'Η επισκευή δημιουργήθηκε επιτυχώς',
                'data' => $newRepair
            ];

            $response->getBody()->write(json_encode($successResponse, JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            // Διαχείριση σφαλμάτων
            $errorResponse = [
                'status' => 'error',
                'message' => 'Σφάλμα κατά τη δημιουργία της επισκευής: ' . $e->getMessage()
            ];

            $response->getBody()->write(json_encode($errorResponse, JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
