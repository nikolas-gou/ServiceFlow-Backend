<?php

namespace App\Repositories;

use App\Models\Motor;
use App\Models\MotorCrossSectionLinks;
use PDO;

class MotorRepository
{
    private $conn;

    public function __construct(PDO $pdo)
    {
        $this->conn = $pdo;
    }

    public function getAll(): array
    {
        $query = "SELECT m.*, COUNT(r.id) as repairs_count 
                  FROM motors m 
                  INNER JOIN repairs r ON m.id = r.motor_id 
                  WHERE r.deleted_at IS NULL 
                  GROUP BY m.id
                  ORDER BY m.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $motorsData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $motors = [];
        foreach ($motorsData as $motorData) {
            $motor = new Motor($motorData);
            $motorDataFormatted = $motor->toFrontendFormat();
            $motorDataFormatted['repairsCount'] = (int) ($motorData['repairs_count'] ?? 0);
            $motors[] = $motorDataFormatted;
        }

        return $motors;
    }

    public function getRepairsByMotorId($motorId): array
    {
        $query = "SELECT r.*, 
                  c.name as customer_name, c.type as customer_type, c.phone as customer_phone, c.email as customer_email
                  FROM repairs r
                  LEFT JOIN customers c ON r.customer_id = c.id
                  WHERE r.motor_id = :motor_id AND r.deleted_at IS NULL
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':motor_id', $motorId, \PDO::PARAM_INT);
        $stmt->execute();
        $repairsData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $repairs = [];
        foreach ($repairsData as $repairData) {
            $repair = [];
            $repair['id'] = $repairData['id'];
            $repair['motorID'] = $repairData['motor_id'];
            $repair['customerID'] = $repairData['customer_id'];
            $repair['repairStatus'] = $repairData['repair_status'];
            $repair['description'] = $repairData['description'];
            $repair['cost'] = $repairData['cost'];
            $repair['createdAt'] = $repairData['created_at'];
            $repair['isArrived'] = $repairData['is_arrived'];
            $repair['estimatedIsComplete'] = $repairData['estimated_is_complete'];
            $repair['customer'] = [
                'id' => $repairData['customer_id'],
                'name' => $repairData['customer_name'],
                'type' => $repairData['customer_type'],
                'phone' => $repairData['customer_phone'],
                'email' => $repairData['customer_email'],
            ];

            // Φέρνουμε τα common faults για αυτή την επισκευή
            $faultQuery = "SELECT cf.id, cf.name 
                          FROM repair_fault_links rfl
                          INNER JOIN common_faults cf ON rfl.common_fault_id = cf.id
                          WHERE rfl.repair_id = :repair_id";
            $faultStmt = $this->conn->prepare($faultQuery);
            $faultStmt->bindParam(':repair_id', $repairData['id'], \PDO::PARAM_INT);
            $faultStmt->execute();
            $repair['repairFaultLinks'] = $faultStmt->fetchAll(\PDO::FETCH_ASSOC);

            $repairs[] = $repair;
        }

        return $repairs;
    }

    public function getMotorById($id): ?array
    {
        $query = "SELECT m.* FROM motors m 
                  INNER JOIN repairs r ON m.id = r.motor_id 
                  WHERE m.id = :id AND r.deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $motorData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$motorData) {
            return null;
        }
        
        // Φέρνουμε τα motor_cross_section_links
        $linkQuery = "SELECT * FROM motor_cross_section_links WHERE motor_id = :motor_id";
        $linkStmt = $this->conn->prepare($linkQuery);
        $linkStmt->bindParam(':motor_id', $id, \PDO::PARAM_INT);
        $linkStmt->execute();

        $links = [];
        while ($linkRow = $linkStmt->fetch(\PDO::FETCH_ASSOC)) {
            $links[] = new MotorCrossSectionLinks($linkRow);
        }

        // Ανάθεση των συνδέσμων στο αντικείμενο motor
        $motor = new Motor($motorData);
        $motor->motorCrossSectionLinks = $links;

        return $motor->toFrontendFormat();
    }

    public function getTopBrands(int $limit = 5): array
    {
        $stmt = $this->conn->prepare("
            SELECT 
                m.manufacturer, 
                COUNT(*) as count
            FROM motors m
            INNER JOIN repairs r ON m.id = r.motor_id 
            WHERE r.deleted_at IS NULL 
            AND m.manufacturer IS NOT NULL 
            AND m.manufacturer != '' 
            AND TRIM(m.manufacturer) != '' 
            GROUP BY m.manufacturer 
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
    }

    public function createMotor(Motor $motor, $customer_id): int
    {
        $motorQuery = "INSERT
                        INTO motors (customer_id, serial_number, description, manufacturer, kw, hp, rpm,
                        step, half_step, helper_step, helper_half_step, spiral, half_spiral,
                        helper_spiral, helper_half_spiral, connectionism, volt, amps, poles,
                        coils_count, half_coils_count, helper_coils_count, helper_half_coils_count,
                        type_of_motor, type_of_volt, type_of_step, created_at)
                        VALUES (:customer_id, :serial_number, :description, :manufacturer, :kw, :hp, :rpm,
                        :step, :half_step, :helper_step, :helper_half_step, :spiral, :half_spiral,
                        :helper_spiral, :helper_half_spiral, :connectionism, :volt, :amps, :poles,
                        :coils_count, :half_coils_count, :helper_coils_count, :helper_half_coils_count,
                        :type_of_motor, :type_of_volt, :type_of_step, :created_at)";
        
        $motorStmt = $this->conn->prepare($motorQuery);
        $motorStmt->bindParam(':customer_id', $customer_id);
        $motorStmt->bindParam(':serial_number', $motor->serial_number);
        $motorStmt->bindParam(':description', $motor->description);
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
        $motorStmt->bindParam(':amps', $motor->amps);
        $motorStmt->bindParam(':poles', $motor->poles);
        $motorStmt->bindParam(':coils_count', $motor->coils_count);
        $motorStmt->bindParam(':half_coils_count', $motor->half_coils_count);
        $motorStmt->bindParam(':helper_coils_count', $motor->helper_coils_count);
        $motorStmt->bindParam(':helper_half_coils_count', $motor->helper_half_coils_count);
        
        $motorStmt->bindParam(':type_of_motor', $motor->type_of_motor);
        $motorStmt->bindParam(':type_of_volt', $motor->type_of_volt);
        $motorStmt->bindParam(':type_of_step', $motor->type_of_step);
        
        // Διόρθωση datetime format για MySQL
        $createdAt = $motor->created_at;
        if ($createdAt) {
            // Μετατροπή από ISO format σε MySQL format
            $date = new \DateTime($createdAt);
            $createdAt = $date->format('Y-m-d H:i:s');
        } else {
            $createdAt = date('Y-m-d H:i:s');
        }
        $motorStmt->bindParam(':created_at', $createdAt);

        $motorStmt->execute();
        return (int) $this->conn->lastInsertId();
    }

    public function updateMotor(Motor $motor, $customer_id = null)
    {
        try {
            $motorQuery = "UPDATE motors SET 
                customer_id = :customer_id,
                serial_number = :serial_number,
                description = :description,
                manufacturer = :manufacturer,
                kw = :kw,
                hp = :hp,
                rpm = :rpm,
                step = :step,
                half_step = :half_step,
                helper_step = :helper_step,
                helper_half_step = :helper_half_step,
                spiral = :spiral,
                half_spiral = :half_spiral,
                helper_spiral = :helper_spiral,
                helper_half_spiral = :helper_half_spiral,
                connectionism = :connectionism,
                volt = :volt,
                amps = :amps,
                poles = :poles,
                coils_count = :coils_count,
                half_coils_count = :half_coils_count,
                helper_coils_count = :helper_coils_count,
                helper_half_coils_count = :helper_half_coils_count,
                type_of_motor = :type_of_motor,
                type_of_volt = :type_of_volt,
                type_of_step = :type_of_step
                WHERE id = :id";
            
            $motorStmt = $this->conn->prepare($motorQuery);

            $motorStmt->bindParam(':id', $motor->id, \PDO::PARAM_INT);
            $motorStmt->bindParam(':customer_id', $customer_id, \PDO::PARAM_INT);
            $motorStmt->bindParam(':serial_number', $motor->serial_number);
            $motorStmt->bindParam(':description', $motor->description);
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
            $motorStmt->bindParam(':amps', $motor->amps);
            $motorStmt->bindParam(':poles', $motor->poles);
            $motorStmt->bindParam(':coils_count', $motor->coils_count);
            $motorStmt->bindParam(':half_coils_count', $motor->half_coils_count);
            $motorStmt->bindParam(':helper_coils_count', $motor->helper_coils_count);
            $motorStmt->bindParam(':helper_half_coils_count', $motor->helper_half_coils_count);
            $motorStmt->bindParam(':type_of_motor', $motor->type_of_motor);
            $motorStmt->bindParam(':type_of_volt', $motor->type_of_volt);
            $motorStmt->bindParam(':type_of_step', $motor->type_of_step);
            
            $motorStmt->execute();

            // Ενημέρωση των motor cross section links
            if (isset($motor->motorCrossSectionLinks) && is_array($motor->motorCrossSectionLinks)) {
                // Διαγραφή παλιών συνδέσεων
                $deleteQuery = "DELETE FROM motor_cross_section_links WHERE motor_id = :motor_id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->bindParam(':motor_id', $motor->id, \PDO::PARAM_INT);
                $deleteStmt->execute();

                // Προσθήκη νέων συνδέσεων
                foreach ($motor->motorCrossSectionLinks as $link) {
                    $insertQuery = "INSERT INTO motor_cross_section_links (motor_id, cross_section, type)
                        VALUES (:motor_id, :cross_section, :type)";
                    $insertStmt = $this->conn->prepare($insertQuery);
                    $insertStmt->bindParam(':motor_id', $motor->id, \PDO::PARAM_INT);
                    $insertStmt->bindParam(':cross_section', $link->cross_section);
                    $insertStmt->bindParam(':type', $link->type);
                    $insertStmt->execute();
                }
            }

            return $this->getMotorById($motor->id);
            
        } catch (\Exception $e) {
            error_log("Error in updateMotor: " . $e->getMessage());
            throw $e;
        }
    }

    // Statistics
    public function getTotalCountFiltered(array $filters = []): int
    {
        $sql = "
            SELECT COUNT(DISTINCT m.id)
            FROM motors m
            INNER JOIN repairs r ON m.id = r.motor_id
            WHERE r.deleted_at IS NULL
        ";
        $params = [];
        foreach ($filters as $field => $value) {
            $sql .= " AND $field = ?";
            $params[] = $value;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
    
    public function getMonthlyTrendsFiltered(array $filters = []): array
    {
        $currentYear = date('Y');
        $currentMonth = (int) date('m');
        
        $sql = "
            SELECT
                MONTH(m.created_at) as month,
                COUNT(DISTINCT m.id) as count
            FROM motors m
            INNER JOIN repairs r ON m.id = r.motor_id
            WHERE r.deleted_at IS NULL
            AND YEAR(m.created_at) = ?
            AND MONTH(m.created_at) <= ?
        ";
        $params = [$currentYear, $currentMonth];
        foreach ($filters as $field => $value) {
            $sql .= " AND $field = ?";
            $params[] = $value;
        }
        $sql .= " GROUP BY MONTH(m.created_at) ORDER BY month ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $monthlyData = [];
        for ($month = 1; $month <= $currentMonth; $month++) {
            $monthlyData[] = 0;
        }
        foreach ($results as $row) {
            $monthIndex = (int)$row['month'] - 1;
            $monthlyData[$monthIndex] = (int)$row['count'];
        }
        return $monthlyData;
    }

    public function getCountByMonth(string $monthKey): int
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT m.id)
            FROM motors m
            INNER JOIN repairs r ON m.id = r.motor_id
            WHERE r.deleted_at IS NULL
            AND DATE_FORMAT(m.created_at, '%Y-%m') = ?
        ");
        $stmt->execute([$monthKey]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Επιστρέφει τα συνολικά counts ανά τύπο σύνδεσης
     */
    public function getConnectionismCounts(): array
    {
        $stmt = $this->conn->prepare("
            SELECT connectionism, COUNT(DISTINCT m.id) as count
            FROM motors m
            INNER JOIN repairs r ON m.id = r.motor_id
            WHERE r.deleted_at IS NULL
            GROUP BY connectionism
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Επιστρέφει τα μηνιαία δεδομένα για συγκεκριμένο τύπο σύνδεσης
     */
    public function getMonthlyConnectionismData(string $connectionismType, int $year, int $month): array
    {
        $stmt = $this->conn->prepare("
            SELECT
                MONTH(m.created_at) as month,
                COUNT(DISTINCT m.id) as count
            FROM motors m
            INNER JOIN repairs r ON m.id = r.motor_id
            WHERE r.deleted_at IS NULL
            AND m.connectionism = ?
            AND YEAR(m.created_at) = ?
            AND MONTH(m.created_at) <= ?
            GROUP BY MONTH(m.created_at)
            ORDER BY month ASC
        ");
        $stmt->execute([$connectionismType, $year, $month]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** Επιστρέφει τα προτεινόμενα βήματα, που εχουν καταχωρηθεί(χωρις duplicates) */
    public function getSuggestedSteps(): array
    {
        $stmt = $this->conn->prepare("
            SELECT DISTINCT step 
            FROM motors
            WHERE step IS NOT NULL
            ORDER BY step
        ");

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    public function getSuggestedManufacturers(): array
    {
        $query = "SELECT DISTINCT m.manufacturer 
                  FROM motors m 
                  INNER JOIN repairs r ON m.id = r.motor_id  
                  WHERE r.deleted_at IS NULL 
                  AND m.manufacturer IS NOT NULL 
                  AND m.manufacturer != '' 
                  ORDER BY m.manufacturer";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getSuggestedDescriptions(): array
    {
        $query = "
            SELECT DISTINCT m.description
            FROM motors m
            INNER JOIN repairs r ON m.id = r.motor_id  
            WHERE r.deleted_at IS NULL 
            AND m.description IS NOT NULL 
            AND m.description != '' 
            ORDER BY m.description
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}