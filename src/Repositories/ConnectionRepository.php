<?php

namespace App\Repositories;

use App\Models\Connection;
use PDO;

class ConnectionRepository
{
    private $conn;

    public function __construct(PDO $pdo)
    {
        $this->conn = $pdo;
    }

    public function getAll()
    {
        $query = "SELECT * FROM connections ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $connectionsData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $connections = [];
        foreach ($connectionsData as $connectionData) {
            $connection = new Connection($connectionData);
            $connections[] = $connection->toFrontendFormat();
        }

        return $connections;
    }

    public function getPaginated(array $params = [])
    {
        $page = isset($params['page']) ? max(1, (int)$params['page']) : 1;
        $perPage = isset($params['perPage']) ? max(1, min(100, (int)$params['perPage'])) : 20;
        $offset = ($page - 1) * $perPage;

        $search = $params['search'] ?? null;
        $connectionType = $params['connectionType'] ?? null;
        $poles = $params['poles'] ?? null;
        $rpm = $params['rpm'] ?? null;
        $typeOfVolt = $params['typeOfVolt'] ?? null;

        $sortBy = $params['sortBy'] ?? 'created_at';
        $sortOrder = strtoupper($params['sortOrder'] ?? 'DESC');
        $sortOrder = in_array($sortOrder, ['ASC', 'DESC']) ? $sortOrder : 'DESC';

        $where = [];
        $bindings = [];

        if ($search && !empty($search) && is_string($search)) {
            $searchValue = "%{$search}%";
            $where[] = "(connection_type LIKE :search1 OR description LIKE :search2 OR poles LIKE :search3 OR rpm LIKE :search4)";
            $bindings[':search1'] = $searchValue;
            $bindings[':search2'] = $searchValue;
            $bindings[':search3'] = $searchValue;
            $bindings[':search4'] = $searchValue;
        }

        if ($connectionType && !empty($connectionType)) {
            $where[] = "connection_type = :connectionType";
            $bindings[':connectionType'] = $connectionType;
        }

        if ($poles !== null && $poles !== '') {
            $where[] = "poles = :poles";
            $bindings[':poles'] = $poles;
        }

        if ($rpm !== null && $rpm !== '') {
            $where[] = "rpm = :rpm";
            $bindings[':rpm'] = $rpm;
        }

        if ($typeOfVolt !== null && $typeOfVolt !== '') {
            $where[] = "type_of_volt = :typeOfVolt";
            $bindings[':typeOfVolt'] = $typeOfVolt;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $countQuery = "SELECT COUNT(*) as total FROM connections {$whereClause}";
        $countStmt = $this->conn->prepare($countQuery);
        foreach ($bindings as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $result = $countStmt->fetch(\PDO::FETCH_ASSOC);
        $totalItems = $result ? (int)$result['total'] : 0;

        $allowedSortColumns = ['created_at', 'id', 'connection_type', 'poles', 'rpm'];
        $sortByColumn = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_at';

        $dataQuery = "SELECT * FROM connections {$whereClause} ORDER BY {$sortByColumn} {$sortOrder} LIMIT :limit OFFSET :offset";
        $dataStmt = $this->conn->prepare($dataQuery);
        foreach ($bindings as $key => $value) {
            $dataStmt->bindValue($key, $value);
        }
        $dataStmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $dataStmt->execute();
        $connectionsData = $dataStmt->fetchAll(\PDO::FETCH_ASSOC);

        $connections = [];
        foreach ($connectionsData as $connectionData) {
            $connection = new Connection($connectionData);
            $connections[] = $connection->toFrontendFormat();
        }

        $totalPages = (int)ceil($totalItems / $perPage);
        $from = $totalItems > 0 ? $offset + 1 : 0;
        $to = min($offset + $perPage, $totalItems);

        return [
            'data' => $connections,
            'pagination' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
                'from' => $from,
                'to' => $to,
                'hasNextPage' => $page < $totalPages,
                'hasPrevPage' => $page > 1
            ]
        ];
    }

    public function getConnectionById($id)
    {
        $query = "SELECT * FROM connections WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $connectionData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$connectionData) {
            return null;
        }

        $connection = new Connection($connectionData);
        return $connection->toFrontendFormat();
    }

    public function createConnection($connectionData)
    {
        $query = "INSERT INTO connections (connection_type, type_of_volt, poles, rpm, step, half_step, type_of_step, coils, caves, description, created_at)
                  VALUES (:connection_type, :type_of_volt, :poles, :rpm, :step, :half_step, :type_of_step, :coils, :caves, :description, :created_at)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':connection_type', $connectionData->connection_type);
        $stmt->bindParam(':type_of_volt', $connectionData->type_of_volt);
        $stmt->bindParam(':poles', $connectionData->poles);
        $stmt->bindParam(':rpm', $connectionData->rpm);
        $stmt->bindParam(':step', $connectionData->step);
        $stmt->bindParam(':half_step', $connectionData->half_step);
        $stmt->bindParam(':type_of_step', $connectionData->type_of_step);
        $stmt->bindParam(':coils', $connectionData->coils);
        $stmt->bindParam(':caves', $connectionData->caves);
        $stmt->bindParam(':description', $connectionData->description);

        $createdAt = $connectionData->created_at;
        if ($createdAt) {
            $date = new \DateTime($createdAt);
            $createdAt = $date->format('Y-m-d H:i:s');
        } else {
            $createdAt = date('Y-m-d H:i:s');
        }
        $stmt->bindParam(':created_at', $createdAt);

        $stmt->execute();
        $id = $this->conn->lastInsertId();

        return $this->getConnectionById($id);
    }

    public function updateConnection($id, $connectionData)
    {
        $query = "UPDATE connections SET
                    connection_type = :connection_type,
                    type_of_volt = :type_of_volt,
                    poles = :poles,
                    rpm = :rpm,
                    step = :step,
                    half_step = :half_step,
                    type_of_step = :type_of_step,
                    coils = :coils,
                    caves = :caves,
                    description = :description
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':connection_type', $connectionData->connection_type);
        $stmt->bindParam(':type_of_volt', $connectionData->type_of_volt);
        $stmt->bindParam(':poles', $connectionData->poles);
        $stmt->bindParam(':rpm', $connectionData->rpm);
        $stmt->bindParam(':step', $connectionData->step);
        $stmt->bindParam(':half_step', $connectionData->half_step);
        $stmt->bindParam(':type_of_step', $connectionData->type_of_step);
        $stmt->bindParam(':coils', $connectionData->coils);
        $stmt->bindParam(':caves', $connectionData->caves);
        $stmt->bindParam(':description', $connectionData->description);

        $stmt->execute();

        return $this->getConnectionById($id);
    }

    public function deleteConnection($id)
    {
        $query = "DELETE FROM connections WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
