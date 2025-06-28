<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\MotorCrossSectionLinks;

class MotorCrossSectionLinksRepository
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
        $query = "SELECT * FROM motor_cross_section_links";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $motorCrossSectionLinks = [];
        foreach ($motorCrossSectionLinksData as $motorCrossSectionLinkData) {
            $motorCrossSectionLinks[] = new MotorCrossSectionLinks($motorCrossSectionLinkData);
        }

        return $motorCrossSectionLinks;
    }

    // future functions 
    public function getMotorCrossSectionLinksById($id)
    {
        $query = "SELECT * FROM motor_cross_section_links WHERE motor_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $motorCrossSectionLinksData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$motorCrossSectionLinksData) {
            return null;
        }

        return new MotorCrossSectionLinks($motorCrossSectionLinksData);
    }
}
