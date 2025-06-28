<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Motor;
use App\Models\MotorCrossSectionLinks;

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

        $links = [];
        while ($linkRow = $linkStmt->fetch(\PDO::FETCH_ASSOC)) {
            $links[] = new MotorCrossSectionLinks($linkRow);
        }

        // 3. Ανάθεση των συνδέσμων στο αντικείμενο motor
        $motor = new Motor($motorData);
        $motor->motorCrossSectionLinks = $links;

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

    public function getTopBrands(int $limit = 5): array
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    manufacturer, 
                    COUNT(*) as count
                FROM motors 
                WHERE manufacturer IS NOT NULL 
                AND manufacturer != '' 
                AND TRIM(manufacturer) != ''
                GROUP BY manufacturer 
                ORDER BY count DESC 
                LIMIT ?
            ");
            
            $stmt->bindParam(1, $limit, \PDO::PARAM_INT);
            $stmt->execute();
            
            $results = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $results[] = [
                    'manufacturer' => trim($row['manufacturer']),
                    'count' => (int) $row['count']
                ];
            }
            
            return $results;
            
        } catch (\Exception $e) {
            error_log("Error in getTopBrands: " . $e->getMessage());
            return [];
        }
    }

    // convert to getAll in a little bit
    public function createMotor(Motor $motor, $customer_id)
    {
        $motorQuery = file_get_contents(__DIR__ . "/../Queries/Repair/createMotor.sql");
        $motorStmt = $this->conn->prepare($motorQuery);
        $motorStmt->bindParam(':customer_id', $customer_id);
        $motorStmt->bindParam(':serial_number', $motor->serial_number);
        $motorStmt->bindParam(':manufacturer', $motor->manufacturer);
        $motorStmt->bindParam(':kw', $motor->kw);
        $motorStmt->bindParam(':hp', $motor->hp);
        $motorStmt->bindParam(':rpm', $motor->rpm);
        $motorStmt->bindParam(':step', $motor->step);
        $motorStmt->bindParam(':half_step', $motor->half_step);
        $motorStmt->bindParam(':helper_step', $motor->helper_step);
        $motorStmt->bindParam(':helper_half_step', $motor->helper_half_step);
        $motorStmt->bindParam(':spiral', $motor->spiral);
        $motorStmt->bindParam(':half_spiral', $motor->half_spiral);
        $motorStmt->bindParam(':helper_spiral', $motor->helper_spiral);
        $motorStmt->bindParam(':helper_half_spiral', $motor->helper_half_spiral);
        $motorStmt->bindParam(':connectionism', $motor->connectionism);
        $motorStmt->bindParam(':volt', $motor->volt);
        $motorStmt->bindParam(':poles', $motor->poles);
        $motorStmt->bindParam(':how_many_coils_with', $motor->how_many_coils_with);
        $motorStmt->bindParam(':type_of_motor', $motor->type_of_motor);
        $motorStmt->bindParam(':type_of_volt', $motor->type_of_volt);
        $motorStmt->bindParam(':type_of_step', $motor->type_of_step);
        $motorStmt->bindParam(':created_at', $motor->created_at);
        $motorStmt->execute();
        return $this->conn->lastInsertId();
    }

    // Statistics
     public function getTotalCount(): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM motors");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getMonthlyTrends(): array
    {
        $currentYear = date('Y');
        $currentMonth = (int) date('m');
        
        $stmt = $this->conn->prepare("
            SELECT 
                MONTH(created_at) as month,
                COUNT(*) as count
            FROM motors 
            WHERE YEAR(created_at) = ? 
            AND MONTH(created_at) <= ?
            GROUP BY MONTH(created_at)
            ORDER BY month ASC
        ");
        
        $stmt->execute([$currentYear, $currentMonth]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Δημιουργία array με όλους τους μήνες (0-based: index 0 = Ιανουάριος)
        $monthlyData = [];
        for ($month = 1; $month <= $currentMonth; $month++) {
            $monthlyData[] = 0; // Default value για κάθε μήνα
        }
        
        // Συμπλήρωση με πραγματικά δεδομένα
        foreach ($results as $row) {
            $monthIndex = (int)$row['month'] - 1; // Μετατροπή σε 0-based index
            $monthlyData[$monthIndex] = (int)$row['count'];
        }
        
        return $monthlyData;
    }

    public function getCountByMonth(string $monthKey): int
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM motors 
            WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
        ");
        $stmt->execute([$monthKey]);
        return (int) $stmt->fetchColumn();
    }

}
