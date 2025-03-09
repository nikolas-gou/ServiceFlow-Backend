<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\Motor;

class MotorRepository {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // convert to getAll in a little bit
    public function getAll() {
        $query = "SELECT * FROM motors";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $motors = [];
        foreach ($motorsData as $motorData) {
            $motors[] = new Motor($motorData);
        }
        
        return $motors;
    }

    // future functions 
    public function getMotorById($id) {
            $query = "SELECT * FROM motors WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $motorData = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$motorData) {
                return null;
            }
            
            return new Motor($motorData);
    }
}