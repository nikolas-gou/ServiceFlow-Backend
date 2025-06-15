<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Motor;
use App\Models\Motor_Cross_Section_Links;

class MotorRepository
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // convert to getAll in a little bit
    public function getAll()
    {
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
    public function getMotorById($id)
    {
        $query = "SELECT * FROM motors WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $motorData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$motorData) {
            return null;
        }
        // 2. Φέρνουμε τα motor_cross_section_links
        $linkQuery = "SELECT * FROM motor_cross_section_links WHERE motor_id = :motor_id";
        $linkStmt = $this->conn->prepare($linkQuery);
        $linkStmt->bindParam(':motor_id', $id, \PDO::PARAM_INT);
        $linkStmt->execute();

        $linksData = $linkStmt->fetchAll(\PDO::FETCH_ASSOC);

        $links = array_map(function ($linkRow) {
            return new Motor_Cross_Section_Links($linkRow);
        }, $linksData);

        // 3. Ανάθεση των συνδέσμων στο αντικείμενο motor
        $motor = new Motor($motorData);
        $motor->motor_cross_section_links = $links;

        return $motor;
    }

    public function getAllBrands()
    {
        $query = "SELECT distinct manufacturer FROM motors";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);

        return $motorsData;
    }
}
