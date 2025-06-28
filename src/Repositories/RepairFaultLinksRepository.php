<?php

namespace App\Repositories;

use App\Models\RepairFaultLinks;
use PDO;

class RepairFaultLinksRepository
{
    private $conn;

    public function __construct(PDO $pdo)
    {
        $this->conn = $pdo;
    }

    public function getAll(): array
    {
        $query = "SELECT * FROM repair_fault_links";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => (new RepairFaultLinks($row))->toFrontendFormat(), $results);
    }

    public function getByRepairId(int $repairId): array
    {
        $query = "SELECT * FROM repair_fault_links WHERE repair_id = :repair_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':repair_id', $repairId, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => (new RepairFaultLinks($row))->toFrontendFormat(), $results);
    }
}
