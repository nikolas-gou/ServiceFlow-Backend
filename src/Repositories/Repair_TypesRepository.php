<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\Repair_Types;

class Repair_TypesRepository {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll() {
        $query = "SELECT * FROM repair_types";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $repair_types = [];
        foreach ($repair_typesData as $repair_typeData) {
            $repair_types[] = new Repair_Types($repair_typeData);
        }
    }

}