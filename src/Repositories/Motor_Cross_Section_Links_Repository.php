<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Motor_Cross_Section_Links;

class Motor_Cross_Section_Links_Repository
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

        $motor_cross_section_links = [];
        foreach ($motor_cross_section_linksData as $motor_cross_section_linkData) {
            $motor_cross_section_links[] = new Motor_Cross_Section_Links($motor_cross_section_linkData);
        }

        return $motor_cross_section_links;
    }

    // future functions 
    public function getMotorCrossSectionLinksById($id)
    {
        $query = "SELECT * FROM motor_cross_section_links WHERE motor_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $motor_cross_section_linksData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$motor_cross_section_linksData) {
            return null;
        }

        return new Motor_Cross_Section_Links($motor_cross_section_linksData);
    }
}
