<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Repair_Fault_Links;

class Repair_Fault_Links_Repository
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll(): array
    {
        $query = "SELECT * FROM repair_fault_links";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => new Repair_Fault_Links($row), $results);
    }

    public function getByRepairId(int $repairId): array
    {
        $query = "SELECT * FROM repair_fault_links WHERE repair_id = :repair_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':repair_id', $repairId, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => new Repair_Fault_Links($row), $results);
    }
}
