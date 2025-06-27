<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Repair;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use App\Models\Motor;
use App\Repositories\MotorRepository;
use App\Models\Motor_Cross_Section_Links;
use App\Models\Repair_Fault_Links;

class RepairRepository
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll(bool $toFrontendFormat = true)
    {
        // Το query που φερνει ολες τις επισκευες
        $query = file_get_contents(__DIR__ . '/../Queries/Repair/getAll.sql');
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $repairsData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $repairs = [];

        foreach ($repairsData as $repairData) {
            // Δημιουργία αντικειμένου Repair
            $repair = new Repair($repairData);

            // Δημιουργία αντικειμένου Customer
            $customerData = [
                'id' => $repairData['customer_id'],
                'type' => $repairData['type'],
                'name' => $repairData['name'],
                'email' => $repairData['email'],
                'phone' => $repairData['phone'],
                'created_at' => $repairData['customer_created_at'],
            ];
            $customer = new Customer($customerData);
            $repair->customer = $customer;

            // Δημιουργία αντικειμένου Motor
            $motorData = [
                'id' => $repairData['motor_id'],
                'serial_number' => $repairData['serial_number'],
                'manufacturer' => $repairData['manufacturer'],
                'kw' => $repairData['kw'],
                'hp' => $repairData['hp'],
                'rpm' => $repairData['rpm'],
                'step' => $repairData['step'],
                'half_step' => $repairData['half_step'],
                'helper_step' => $repairData['helper_step'],
                'helper_half_step' => $repairData['helper_half_step'],
                'spiral' => $repairData['spiral'],
                'half_spiral' => $repairData['half_spiral'],
                'helper_spiral' => $repairData['helper_spiral'],
                'helper_half_spiral' => $repairData['helper_half_spiral'],
                'connectionism' => $repairData['connectionism'],
                'volt' => $repairData['volt'],
                'poles' => $repairData['poles'],
                'type_of_step' => $repairData['type_of_step'],
                'type_of_motor' => $repairData['type_of_motor'],
                'type_of_volt' => $repairData['type_of_volt'],
                'created_at' => $repairData['motor_created_at'],
                'customer_id' => $repairData['motor_customer_id'],
            ];
            $motor = new Motor($motorData);

            // Επεξεργασία των διατομών από JSON
            $crossSectionsJson = $repairData['cross_sections_json'];
            $crossSectionsArray = json_decode($crossSectionsJson, true);

            $motorCrossSectionLinks = [];
            if ($crossSectionsArray && is_array($crossSectionsArray)) {
                foreach ($crossSectionsArray as $crossSectionData) {
                    if ($crossSectionData !== null) {
                        $motorCrossSectionLinks[] = new Motor_Cross_Section_Links($crossSectionData);
                    }
                }
            }

            $motor->motor_cross_section_links = $motorCrossSectionLinks;
            $repair->motor = $motor;

            // Επεξεργασία των βλαβών από JSON
            $repair_fault_linksJSON = $repairData['repair_fault_links_json'];
            $repair_fault_linksArray = json_decode($repair_fault_linksJSON, true);

            $repair_fault_links = [];
            if ($repair_fault_linksArray && is_array($repair_fault_linksArray)) {
                foreach ($repair_fault_linksArray as $repair_fault_linksData) {
                    if ($repair_fault_linksData !== null) {
                        $repair_fault_links[] = new Repair_Fault_Links($repair_fault_linksData);
                    }
                }
            }
            $repair->repair_fault_links = $repair_fault_links;

            if ($toFrontendFormat) {
                $repairs[] = $repair->toFrontendFormat();
            } else {
                $repairs[] = $repair;
            }
        }

        return $repairs;
    }

    // future functions 
    public function getRepairById($id)
    {
        // Φέρνουμε το repair
        $query = "SELECT * FROM repairs WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $repairData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$repairData) {
            return null;
        }

        // Δημιουργούμε το Repair αντικείμενο
        $repair = new Repair($repairData);
        $motor = new MotorRepository();
        $customer = new CustomerRepository();

        // Φέρνουμε το αντίστοιχο Motor
        if (!empty($repair->motor_id)) {
            $repair->motor = $motor->getMotorById($repair->motor_id);
        }

        // Φέρνουμε το αντίστοιχο Motor
        if (!empty($repair->customer_id)) {
            $repair->customer = $customer->getCustomerById($repair->customer_id);
        }

        // Φέρνουμε το αντίστοιχο Motor
        $repairFaultLinksRepo = new Repair_Fault_Links_Repository();
        $repair->repair_fault_links = $repairFaultLinksRepo->getByRepairId($id);

        return $repair;
    }


    public function createNewRepair($repairData)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Έλεγχος αν ο πελάτης υπάρχει ήδη ή δημιουργία νέου πελάτη
            $customer_id = null;
            if (isset($repairData->customer->id) && $repairData->customer->id > 0) {
                // Χρήση υπάρχοντος πελάτη
                $customer_id = $repairData->customer->id;
            } else {
                // Δημιουργία νέου πελάτη
                $customerRepo = new CustomerRepository();
                $customer_id = $customerRepo->createCustomer($repairData->customer);
            }

            // 2. Έλεγχος αν ο κινητήρας υπάρχει ήδη ή δημιουργία νέου κινητήρα
            // Σχεδον παντα δεν θα υπαρχει ιδιος
            $motor_id = null;
            if (isset($repairData->motor->id) && $repairData->motor->id > 0) {
                // Χρήση υπάρχοντος κινητήρα
                $motor_id = $repairData->motor->id;
            } else {
                // Δημιουργία νέου κινητήρα
                $motorRepo = new MotorRepository();
                $motor_id = $motorRepo->createMotor($repairData->motor, $customer_id);
            }

            // // 3. Δημιουργία της επισκευής
            $repairQuery = "INSERT INTO repairs (customer_id, motor_id, description, 
                        repair_status, cost, created_at, is_arrived, estimated_is_complete) 
                        VALUES (:customer_id, :motor_id, :description, :repair_status,
                        :cost, :created_at, :is_arrived, :estimated_is_complete)";
            $repairStmt = $this->conn->prepare($repairQuery);
            $repairStmt->bindParam(':customer_id', $customer_id);
            $repairStmt->bindParam(':motor_id', $motor_id);
            $repairStmt->bindParam(':description', $repairData->description);
            $repairStmt->bindParam(':repair_status', $repairData->repair_status);
            $repairStmt->bindParam(':cost', $repairData->cost);
            $repairStmt->bindParam(':created_at', $repairData->created_at);
            $repairStmt->bindParam(':is_arrived', $repairData->is_arrived);
            $repairStmt->bindParam(':estimated_is_complete', $repairData->estimated_is_complete);
            $repairStmt->execute();
            $repair_id = $this->conn->lastInsertId();

            // 4. Αποθήκευση των τύπων επισκευής (repair types)
            if (isset($repairData->repair_fault_links) && is_array($repairData->repair_fault_links)) {
                foreach ($repairData->repair_fault_links as $repair_fault_link) {
                    $commonFaultQuery = "INSERT INTO repair_fault_links (repair_id, common_fault_id) 
                                    VALUES (:repair_id, :common_fault_id)";
                    $commonFaultStmt = $this->conn->prepare($commonFaultQuery);
                    $commonFaultStmt->bindParam(':repair_id', $repair_id);
                    $commonFaultStmt->bindParam(':common_fault_id', $repair_fault_link->common_fault_id);
                    $commonFaultStmt->execute();
                }
            }

            // 4. Αποθήκευση των τύπων επισκευής (repair types)
            if (isset($repairData->motor->motor_cross_section_links) && is_array($repairData->motor->motor_cross_section_links)) {
                foreach ($repairData->motor->motor_cross_section_links as $motor_cross_section_link) {
                    $motor_cross_section_linksQuery = "INSERT INTO motor_cross_section_links (motor_id, cross_section, type) 
                        VALUES (:motor_id, :cross_section, :type)";
                    $motor_cross_section_linksStmt = $this->conn->prepare($motor_cross_section_linksQuery);
                    $motor_cross_section_linksStmt->bindParam(':motor_id', $motor_id);
                    $motor_cross_section_linksStmt->bindParam(':cross_section', $motor_cross_section_link->cross_section);
                    $motor_cross_section_linksStmt->bindParam(':type', $motor_cross_section_link->type);
                    $motor_cross_section_linksStmt->execute();
                }
            }

            $this->conn->commit();

            // 4. Ανάκτηση πλήρους επισκευής με πελάτη και κινητήρα
            return $this->getRepairById($repair_id);
        } catch (\Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // Statistics
    public function getTotalCount(): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM repairs");
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
            FROM repairs 
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

    public function getMonthlyRevenueTrends(): array
    {
        $currentYear = date('Y');
        $currentMonth = (int) date('m');
        
        $stmt = $this->conn->prepare("
            SELECT 
                MONTH(created_at) as month,
                COALESCE(SUM(cost), 0) as revenue
            FROM repairs 
            WHERE YEAR(created_at) = ? 
            AND MONTH(created_at) <= ?
            /* AND status = 'completed' */
            GROUP BY MONTH(created_at)
            ORDER BY month ASC
        ");
        
        $stmt->execute([$currentYear, $currentMonth]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Δημιουργία array με όλους τους μήνες (0-based: index 0 = Ιανουάριος)
        $monthlyData = [];
        for ($month = 1; $month <= $currentMonth; $month++) {
            $monthlyData[] = 0.0; // Default value για κάθε μήνα
        }
        
        // Συμπλήρωση με πραγματικά δεδομένα
        foreach ($results as $row) {
            $monthIndex = (int)$row['month'] - 1; // Μετατροπή σε 0-based index
            $monthlyData[$monthIndex] = (float)$row['revenue'];
        }
        
        return $monthlyData;
    }

    public function getCountByMonth(string $monthKey): int
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM repairs 
            WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
        ");
        $stmt->execute([$monthKey]);
        return (int) $stmt->fetchColumn();
    }

    public function getRevenueByMonth(string $monthKey): float
    {
        // Υποθέτω ότι έχεις πεδίο 'cost' ή 'price' στον πίνακα repairs
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(cost), 0) 
            FROM repairs 
            WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
            /* AND repairstatus = 'completed' */
        ");
        $stmt->execute([$monthKey]);
        return (float) $stmt->fetchColumn();
    }

    public function getRevenueByYear(string $year): float
    {
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(cost), 0) 
            FROM repairs 
            WHERE YEAR(created_at) = ?
             /* AND repairstatus = 'completed' */
        ");
        $stmt->execute([$year]);
        return (float) $stmt->fetchColumn();
    }

    public function getCountByStatus(): array
    {
        $stmt = $this->conn->prepare("
            SELECT repair_status, COUNT(*) as count
            FROM repairs 
            GROUP BY repair_status
        ");
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = [
                'repair_status' => $row['repair_status'],
                'count' => (int) $row['count']
            ];
        }
        
        return $results;
    }
}
