<?php

namespace App\Repositories;

use App\Models\Repair;
use App\Models\Motor;
use PDO;

class RepairRepository
{
    private $conn;
    private $motorRepository;
    private $customerRepository;
    private $repairFaultLinksRepository;
    private $imageRepository;

    public function __construct(
        PDO $pdo,
        MotorRepository $motorRepository = null,
        CustomerRepository $customerRepository = null,
        RepairFaultLinksRepository $repairFaultLinksRepository = null,
        ImageRepository $imageRepository = null
    ) {
        $this->conn = $pdo;
        $this->motorRepository = $motorRepository;
        $this->customerRepository = $customerRepository;
        $this->repairFaultLinksRepository = $repairFaultLinksRepository;
        $this->imageRepository = $imageRepository;
    }

    public function getAll(bool $toFrontendFormat = true)
    {
        $query = "SELECT * FROM repairs WHERE deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $repairsData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $repairs = [];
        foreach ($repairsData as $repairData) {
            $repair = new Repair($repairData);
            
            // Φέρνουμε το αντίστοιχο Motor
            if (!empty($repair->motor_id)) {
                if ($this->motorRepository) {
                    $repair->motor = $this->motorRepository->getMotorById($repair->motor_id);
                }
            }

            // Φέρνουμε το αντίστοιχο Customer
            if (!empty($repair->customer_id)) {
                if ($this->customerRepository) {
                    $repair->customer = $this->customerRepository->getCustomerById($repair->customer_id);
                }
            }

            // Φέρνουμε τα repair fault links
            if ($this->repairFaultLinksRepository) {
                $repair->repairFaultLinks = $this->repairFaultLinksRepository->getByRepairId($repair->id);
            }

            // Φέρνουμε τα images
            if ($this->imageRepository) {
                $repair->images = $this->imageRepository->getByRepairId($repair->id);
            }


            // Πάντα επιστρέφουμε formatted data για το frontend
            $repairs[] = $repair->toFrontendFormat();
        }

        return $repairs;
    }

    public function getRepairById($id)
    {
        try {
            $query = "SELECT * FROM repairs WHERE id = :id AND deleted_at IS NULL";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $repairData = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$repairData) {
                return null;
            }

            // Δημιουργούμε το Repair αντικείμενο
            $repair = new Repair($repairData);

            // Φέρνουμε το αντίστοιχο Motor
            if (!empty($repair->motor_id) && $this->motorRepository) {
                try {
                    $repair->motor = $this->motorRepository->getMotorById($repair->motor_id);
                } catch (\Exception $e) {
                    error_log("Error fetching motor for repair {$id}: " . $e->getMessage());
                    $repair->motor = null;
                }
            }

            // Φέρνουμε το αντίστοιχο Customer
            if (!empty($repair->customer_id) && $this->customerRepository) {
                try {
                    $repair->customer = $this->customerRepository->getCustomerById($repair->customer_id);
                } catch (\Exception $e) {
                    error_log("Error fetching customer for repair {$id}: " . $e->getMessage());
                    $repair->customer = null;
                }
            }

            // Φέρνουμε τα repair fault links
            if ($this->repairFaultLinksRepository) {
                try {
                    $repair->repairFaultLinks = $this->repairFaultLinksRepository->getByRepairId($id);
                } catch (\Exception $e) {
                    error_log("Error fetching repair fault links for repair {$id}: " . $e->getMessage());
                    $repair->repairFaultLinks = [];
                }
            }

            // Φέρνουμε τα images
            if ($this->imageRepository) {
                try {
                    $repair->images = $this->imageRepository->getByRepairId($id);
                } catch (\Exception $e) {
                    error_log("Error fetching images for repair {$id}: " . $e->getMessage());
                    $repair->images = [];
                }
            }

            // Επιστρέφουμε formatted data για το frontend
            return $repair->toFrontendFormat();
        } catch (\Exception $e) {
            error_log("Error in getRepairById for ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function createNewRepair($repairData)
    {
        try {
            error_log("RepairRepository::createNewRepair - Starting with data: " . json_encode($repairData));
            
            $this->conn->beginTransaction();

            // 1. Έλεγχος αν ο πελάτης υπάρχει ήδη ή δημιουργία νέου πελάτη
            $customer_id = null;
            if (isset($repairData->customer->id) && $repairData->customer->id > 0) {
                // Χρήση υπάρχοντος πελάτη
                $customer_id = $repairData->customer->id;
                error_log("Using existing customer with ID: " . $customer_id);
                
                // Έλεγχος και ενημέρωση στοιχείων πελάτη αν έχουν αλλάξει
                if ($this->customerRepository) {
                    $this->customerRepository->checkAndUpdateCustomerDetails($repairData->customer);
                    error_log("Checked and potentially updated customer details for ID: " . $customer_id);
                }
            } else {
                // Δημιουργία νέου πελάτη
                if ($this->customerRepository) {
                    $customer_id = $this->customerRepository->createCustomer($repairData->customer);
                    error_log("Created new customer with ID: " . $customer_id);
                } else {
                    throw new \Exception('CustomerRepository not available');
                }
            }

            // 2. Έλεγχος αν ο κινητήρας υπάρχει ήδη ή δημιουργία νέου κινητήρα
            $motor_id = null;
            if (isset($repairData->motor->id) && $repairData->motor->id > 0) {
                // Χρήση υπάρχοντος κινητήρα
                $motor_id = $repairData->motor->id;
                error_log("Using existing motor with ID: " . $motor_id);
            } else {
                // Δημιουργία νέου κινητήρα
                if ($this->motorRepository) {
                    $motor_id = $this->motorRepository->createMotor($repairData->motor, $customer_id);
                    error_log("Created new motor with ID: " . $motor_id);
                } else {
                    throw new \Exception('MotorRepository not available');
                }
            }

            // 3. Δημιουργία της επισκευής
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
            
            // Διόρθωση datetime format για MySQL
            $createdAt = $repairData->created_at;
            if ($createdAt) {
                // Μετατροπή από ISO format σε MySQL format
                $date = new \DateTime($createdAt);
                $createdAt = $date->format('Y-m-d H:i:s');
            } else {
                $createdAt = date('Y-m-d H:i:s');
            }
            $repairStmt->bindParam(':created_at', $createdAt);
            
            $repairStmt->bindParam(':is_arrived', $repairData->is_arrived);
            $repairStmt->bindParam(':estimated_is_complete', $repairData->estimated_is_complete);
            $repairStmt->execute();
            $repair_id = $this->conn->lastInsertId();
            
            error_log("Created repair with ID: " . $repair_id);

            // 4. Αποθήκευση των τύπων επισκευής (repair types)
            if (isset($repairData->repairFaultLinks) && is_array($repairData->repairFaultLinks)) {
                foreach ($repairData->repairFaultLinks as $repairFaultLink) {
                    $commonFaultQuery = "INSERT INTO repair_fault_links (repair_id, common_fault_id)
                        VALUES (:repair_id, :common_fault_id)";
                    $commonFaultStmt = $this->conn->prepare($commonFaultQuery);
                    $commonFaultStmt->bindParam(':repair_id', $repair_id);
                    $commonFaultStmt->bindParam(':common_fault_id', $repairFaultLink->common_fault_id);
                    $commonFaultStmt->execute();
                }
                error_log("Saved " . count($repairData->repairFaultLinks) . " repair fault links");
            }

            // 5. Αποθήκευση των motor cross section links
            if (isset($repairData->motor->motorCrossSectionLinks) && is_array($repairData->motor->motorCrossSectionLinks)) {
                foreach ($repairData->motor->motorCrossSectionLinks as $motorCrossSectionLink) {
                    $motorCrossSectionLinksQuery = "INSERT INTO motor_cross_section_links (motor_id, cross_section, type)
                        VALUES (:motor_id, :cross_section, :type)";
                    $motorCrossSectionLinksStmt = $this->conn->prepare($motorCrossSectionLinksQuery);
                    $motorCrossSectionLinksStmt->bindParam(':motor_id', $motor_id);
                    $motorCrossSectionLinksStmt->bindParam(':cross_section', $motorCrossSectionLink->cross_section);
                    $motorCrossSectionLinksStmt->bindParam(':type', $motorCrossSectionLink->type);
                    $motorCrossSectionLinksStmt->execute();
                }
                error_log("Saved " . count($repairData->motor->motorCrossSectionLinks) . " motor cross section links");
            }

            $this->conn->commit();

            // 6. Ανάκτηση πλήρους επισκευής με πελάτη και κινητήρα
            $result = $this->getRepairById($repair_id);
            error_log("Final result: " . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            error_log("Error in createNewRepair: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateRepair($id, $repairData)
    {
        try {
            error_log("RepairRepository::updateRepair - Starting with data: " . json_encode($repairData));
            
            $this->conn->beginTransaction();

            // 1. Ενημέρωση του πελάτη
            $customer_id = null;
            if (isset($repairData->customer->id) && $repairData->customer->id > 0) {
                // Ενημέρωση υπάρχοντος πελάτη
                if ($this->customerRepository) {
                    $this->customerRepository->checkAndUpdateCustomerDetails($repairData->customer);
                    $customer_id = $repairData->customer->id;
                    error_log("Updated existing customer with ID: " . $customer_id);
                }
            } else {
                // Δημιουργία νέου πελάτη
                if ($this->customerRepository) {
                    $customer_id = $this->customerRepository->createCustomer($repairData->customer);
                    error_log("Created new customer with ID: " . $customer_id);
                }
            }

            // 2. Ενημέρωση του κινητήρα
            $motor_id = null;
            if (isset($repairData->motor->id) && $repairData->motor->id > 0) {
                // Ενημέρωση υπάρχοντος κινητήρα
                if ($this->motorRepository) {
                    $this->motorRepository->updateMotor($repairData->motor, $customer_id);
                    $motor_id = $repairData->motor->id;
                    error_log("Updated existing motor with ID: " . $motor_id);
                }
            } else {
                // Δημιουργία νέου κινητήρα
                if ($this->motorRepository) {
                    $motor_id = $this->motorRepository->createMotor($repairData->motor, $customer_id);
                    error_log("Created new motor with ID: " . $motor_id);
                }
            }

            // 3. Ενημέρωση της επισκευής
            $repairQuery = "UPDATE repairs SET 
                customer_id = :customer_id,
                motor_id = :motor_id,
                description = :description,
                repair_status = :repair_status,
                cost = :cost,
                is_arrived = :is_arrived,
                estimated_is_complete = :estimated_is_complete
                WHERE id = :id AND deleted_at IS NULL";

            $repairStmt = $this->conn->prepare($repairQuery);
            $repairStmt->bindParam(':id', $id);
            $repairStmt->bindParam(':customer_id', $customer_id);
            $repairStmt->bindParam(':motor_id', $motor_id);
            $repairStmt->bindParam(':description', $repairData->description);
            $repairStmt->bindParam(':repair_status', $repairData->repair_status);
            $repairStmt->bindParam(':cost', $repairData->cost);
            $repairStmt->bindParam(':is_arrived', $repairData->is_arrived);
            $repairStmt->bindParam(':estimated_is_complete', $repairData->estimated_is_complete);
            $repairStmt->execute();

            // 4. Ενημέρωση των repair fault links
            if (!empty($repairData->repairFaultLinks)) {
                // Διαγραφή παλιών συνδέσεων
                $deleteQuery = "DELETE FROM repair_fault_links WHERE repair_id = :repair_id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->bindParam(':repair_id', $id);
                $deleteStmt->execute();

                // Προσθήκη νέων συνδέσεων
                foreach ($repairData->repairFaultLinks as $link) {
                    $insertQuery = "INSERT INTO repair_fault_links (repair_id, common_fault_id)
                        VALUES (:repair_id, :common_fault_id)";
                    $insertStmt = $this->conn->prepare($insertQuery);
                    $insertStmt->bindParam(':repair_id', $id);
                    $insertStmt->bindParam(':common_fault_id', $link->common_fault_id);
                    $insertStmt->execute();
                }
            }

            $this->conn->commit();

            // Επιστροφή της ενημερωμένης επισκευής
            return $this->getRepairById($id);
            
        } catch (\Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error in updateRepair: " . $e->getMessage());
            throw $e;
        }
    }

    // Statistics
    public function getTotalCount(): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM repairs WHERE deleted_at IS NULL");
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
            AND deleted_at IS NULL
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
            AND deleted_at IS NULL
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
            AND deleted_at IS NULL
        ");
        $stmt->execute([$monthKey]);
        return (int) $stmt->fetchColumn();
    }

    public function getRevenueByYear(string $year): float
    {
        $stmt = $this->conn->prepare("
            SELECT COALESCE(SUM(cost), 0) 
            FROM repairs 
            WHERE YEAR(created_at) = ?
            AND deleted_at IS NULL
             /* AND repairstatus = 'completed' */
        ");
        $stmt->execute([$year]);
        return (float) $stmt->fetchColumn();
    }


    public function softDelete($id)
    {
        try {
            $query = "UPDATE repairs SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error in softDelete for ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }
}
