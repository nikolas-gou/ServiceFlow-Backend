<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\RepairRepository;
use App\Models\Repair;
use App\Helpers\ResponseHelper;

class RepairController
{
    private $repairRepository;

    public function __construct(RepairRepository $repairRepository)
    {
        $this->repairRepository = $repairRepository;
    }

    public function getAll(Request $request, Response $response): Response
    {
        try {
            $repairs = $this->repairRepository->getAll();
            return ResponseHelper::success($response, $repairs, 'Repairs retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Failed to retrieve repairs: ' . $e->getMessage());
        }
    }

    public function getRepairById(Request $request, Response $response, $args): Response
    {
        try {
            $repair = $this->repairRepository->getRepairById($args['id']);
            
            if (!$repair) {
                return ResponseHelper::notFound($response, 'Repair not found');
            }
            
            return ResponseHelper::success($response, $repair, 'Repair retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Failed to retrieve repair: ' . $e->getMessage());
        }
    }

    public function createRepair(Request $request, Response $response): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);

            if (!isset($data['repair']) || !isset($data['repair']['customer']) || !isset($data['repair']['motor'])) {
                return ResponseHelper::validationError($response, ['Λείπουν απαραίτητα δεδομένα (repair, customer ή motor)']);
            }

            $repair = Repair::fromFrontendFormat($data['repair']);

            error_log("Creating repair with data: " . json_encode($data));

            $newRepair = $this->repairRepository->createNewRepair($repair);

            error_log("Created repair result: " . json_encode($newRepair));

            return ResponseHelper::success($response, $newRepair, 'Η επισκευή δημιουργήθηκε επιτυχώς', 201);
        } catch (\Exception $e) {
            error_log("Error creating repair: " . $e->getMessage());
            return ResponseHelper::serverError($response, 'Σφάλμα κατά τη δημιουργία της επισκευής: ' . $e->getMessage());
        }
    }

    public function softDelete(Request $request, Response $response, $args): Response
    {
        try {
            $id = $args['id'];
            $success = $this->repairRepository->softDelete($id);
            
            if (!$success) {
                return ResponseHelper::notFound($response, 'Η επισκευή δεν βρέθηκε');
            }
            
            return ResponseHelper::success($response, null, 'Η επισκευή μεταφέρθηκε στον κάδο ανακύκλωσης');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Σφάλμα κατά τη διαγραφή της επισκευής: ' . $e->getMessage());
        }
    }

    public function updateRepair(Request $request, Response $response, $args): Response
    {
        try {
            $id = $args['id'];
            $data = json_decode($request->getBody()->getContents(), true);

            if (!isset($data['repair']) || !isset($data['customer']) || !isset($data['motor'])) {
                return ResponseHelper::validationError($response, ['Λείπουν απαραίτητα δεδομένα (repair, customer ή motor)']);
            }

            $repair = Repair::fromFrontendFormat($data['repair']);
            
            error_log("Updating repair with data: " . json_encode($data));
            
            $updatedRepair = $this->repairRepository->updateRepair($id, $repair);
            
            error_log("Updated repair result: " . json_encode($updatedRepair));
            
            return ResponseHelper::success($response, $updatedRepair, 'Η επισκευή ενημερώθηκε επιτυχώς');
        } catch (\Exception $e) {
            error_log("Error updating repair: " . $e->getMessage());
            return ResponseHelper::serverError($response, 'Σφάλμα κατά την ενημέρωση της επισκευής: ' . $e->getMessage());
        }
    }
}
