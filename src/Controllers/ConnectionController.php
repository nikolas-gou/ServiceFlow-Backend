<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\ConnectionRepository;
use App\Models\Connection;
use App\Helpers\ResponseHelper;

class ConnectionController
{
    private $connectionRepository;

    public function __construct(ConnectionRepository $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    public function getAll(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();

            $isPaginated = isset($queryParams['page']) || isset($queryParams['perPage']);

            if ($isPaginated) {
                $result = $this->connectionRepository->getPaginated($queryParams);
                return ResponseHelper::success($response, $result['data'], 'Connections retrieved successfully', 200, $result['pagination']);
            } else {
                $connections = $this->connectionRepository->getAll();
                return ResponseHelper::success($response, $connections, 'Connections retrieved successfully');
            }
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Failed to retrieve connections: ' . $e->getMessage());
        }
    }

    public function getConnectionById(Request $request, Response $response, $args): Response
    {
        try {
            $connection = $this->connectionRepository->getConnectionById($args['id']);

            if (!$connection) {
                return ResponseHelper::notFound($response, 'Connection not found');
            }

            return ResponseHelper::success($response, $connection, 'Connection retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::serverError($response, 'Failed to retrieve connection: ' . $e->getMessage());
        }
    }

    public function createConnection(Request $request, Response $response): Response
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true);

            if (empty($data)) {
                return ResponseHelper::validationError($response, ['Δεν υπάρχουν δεδομένα']);
            }

            $connection = Connection::fromFrontendFormat($data);

            if (!$connection->connection_type || !$connection->poles) {
                return ResponseHelper::validationError($response, ['Τα πεδία connectionType και poles είναι υποχρεωτικά']);
            }

            error_log("Creating connection with data: " . json_encode($data));

            $newConnection = $this->connectionRepository->createConnection($connection);

            error_log("Created connection result: " . json_encode($newConnection));

            return ResponseHelper::success($response, $newConnection, 'Η σύνδεση δημιουργήθηκε επιτυχώς', 201);
        } catch (\Exception $e) {
            error_log("Error creating connection: " . $e->getMessage());
            return ResponseHelper::serverError($response, 'Σφάλμα κατά τη δημιουργία της σύνδεσης: ' . $e->getMessage());
        }
    }

    public function updateConnection(Request $request, Response $response, $args): Response
    {
        try {
            $id = $args['id'];
            $data = json_decode($request->getBody()->getContents(), true);

            if (empty($data)) {
                return ResponseHelper::validationError($response, ['Δεν υπάρχουν δεδομένα']);
            }

            $existing = $this->connectionRepository->getConnectionById($id);
            if (!$existing) {
                return ResponseHelper::notFound($response, 'Η σύνδεση δεν βρέθηκε');
            }

            $connection = Connection::fromFrontendFormat($data);

            error_log("Updating connection with data: " . json_encode($data));

            $updatedConnection = $this->connectionRepository->updateConnection($id, $connection);

            error_log("Updated connection result: " . json_encode($updatedConnection));

            return ResponseHelper::success($response, $updatedConnection, 'Η σύνδεση ενημερώθηκε επιτυχώς');
        } catch (\Exception $e) {
            error_log("Error updating connection: " . $e->getMessage());
            return ResponseHelper::serverError($response, 'Σφάλμα κατά την ενημέρωση της σύνδεσης: ' . $e->getMessage());
        }
    }

    public function deleteConnection(Request $request, Response $response, $args): Response
    {
        try {
            $id = $args['id'];

            $existing = $this->connectionRepository->getConnectionById($id);
            if (!$existing) {
                return ResponseHelper::notFound($response, 'Η σύνδεση δεν βρέθηκε');
            }

            $success = $this->connectionRepository->deleteConnection($id);

            if (!$success) {
                return ResponseHelper::serverError($response, 'Αποτυχία διαγραφής της σύνδεσης');
            }

            return ResponseHelper::success($response, null, 'Η σύνδεση διαγράφηκε επιτυχώς');
        } catch (\Exception $e) {
            error_log("Error deleting connection: " . $e->getMessage());
            return ResponseHelper::serverError($response, 'Σφάλμα κατά τη διαγραφή της σύνδεσης: ' . $e->getMessage());
        }
    }
}
