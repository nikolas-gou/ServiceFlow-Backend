<?php

namespace App\Repositories;

use App\Models\MotorCrossSectionLinks;
use PDO;

class MotorCrossSectionLinksRepository
{
    private $conn;

    public function __construct(PDO $pdo)
    {
        $this->conn = $pdo;
    }

    public function getAll(): array
    {
        $query = "SELECT * FROM motor_cross_section_links";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $motorCrossSectionLinksData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $motorCrossSectionLinks = [];
        foreach ($motorCrossSectionLinksData as $motorCrossSectionLinkData) {
            $motorCrossSectionLink = new MotorCrossSectionLinks($motorCrossSectionLinkData);
            $motorCrossSectionLinks[] = $motorCrossSectionLink->toFrontendFormat();
        }

        return $motorCrossSectionLinks;
    }

    public function getMotorCrossSectionLinksById($id): ?array
    {
        $query = "SELECT * FROM motor_cross_section_links WHERE motor_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $motorCrossSectionLinksData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$motorCrossSectionLinksData) {
            return null;
        }

        $motorCrossSectionLink = new MotorCrossSectionLinks($motorCrossSectionLinksData);
        return $motorCrossSectionLink->toFrontendFormat();
    }

    public function getSuggested(): array {
        $query = "
            SELECT DISTINCT cross_section
            FROM motor_cross_section_links 
            WHERE cross_section IS NOT NULL
            AND cross_section != ''
            ORDER BY CAST(SUBSTRING_INDEX(cross_section, '/', 1) AS DECIMAL(10,4))
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $cross_sections = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
        
        return $cross_sections;
    }
}
