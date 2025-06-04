<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\Repair;
use App\Models\Customer;
use App\Models\Motor;

class RepairRepository {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

public function getAll() {
    $query = "
        SELECT repairs.*, 
               customers.id AS customer_id, 
               customers.type, 
               customers.name, 
               customers.email, 
               customers.phone, 
               customers.created_at AS customer_created_at,
               motors.id AS motor_id, 
               motors.serial_number, 
               motors.manufacturer, 
               motors.kw, 
               motors.hp, 
               motors.rpm, 
               motors.step, 
               motors.half_step,
               motors.helper_step,
               motors.helper_half_step,
               motors.spiral, 
               motors.half_spiral,
               motors.helper_spiral,
               motors.helper_half_spiral,
               motors.cross_section, 
               motors.half_cross_section,
               motors.helper_cross_section,
               motors.helper_half_cross_section,
               motors.connectionism, 
               motors.volt, 
               motors.poles,
               motors.type_of_step,
               motors.type_of_motor,
               motors.type_of_volt,
               motors.created_at AS motor_created_at,
               motors.customer_id AS motor_customer_id
        FROM repairs
        INNER JOIN customers ON repairs.customer_id = customers.id
        INNER JOIN motors ON repairs.motor_id = motors.id
        ORDER BY repairs.created_at DESC
    ";

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
            'created_at' => $repairData['created_at'],
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
            'cross_section' => $repairData['cross_section'],
            'half_cross_section' => $repairData['half_cross_section'],
            'helper_cross_section' => $repairData['helper_cross_section'],
            'helper_half_cross_section' => $repairData['helper_half_cross_section'],
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
        $repair->motor = $motor;

        $repairs[] = $repair;
    }

    return $repairs;
}

    // future functions 
    public function getRepairById($id) {
            $query = "SELECT * FROM repairs WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $repairData = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$repairData) {
                return null;
            }
            
            return new Repair($repairData);
    }
    
    public function createNewRepair($repairData, $customerData, $motorData, $common_faults) {
        try {
            $this->conn->beginTransaction();
            
            // 1. Έλεγχος αν ο πελάτης υπάρχει ήδη ή δημιουργία νέου πελάτη
            $customer_id = null;
            if (isset($customerData['id']) && $customerData['id'] > 0) {
                // Χρήση υπάρχοντος πελάτη
                $customer_id = $customerData['id'];
            } else {
                // Δημιουργία νέου πελάτη
                $customerQuery = "INSERT INTO customers (type, name, email, phone, created_at) 
                                VALUES (:type, :name, :email, :phone, :created_at)";
                $customerStmt = $this->conn->prepare($customerQuery);
                $customerStmt->bindParam(':type', $customerData['type']);
                $customerStmt->bindParam(':name', $customerData['name']);
                $customerStmt->bindParam(':email', $customerData['email']);
                $customerStmt->bindParam(':phone', $customerData['phone']);
                $customerStmt->bindParam(':created_at', $customerData['created_at']);
                $customerStmt->execute();
                $customer_id = $this->conn->lastInsertId();
            }
            
            // 2. Έλεγχος αν ο κινητήρας υπάρχει ήδη ή δημιουργία νέου κινητήρα
            // Σχεδον παντα δεν θα υπαρχει ιδιος
            $motor_id = null;
            if (isset($motorData['id']) && $motorData['id'] > 0) {
                // Χρήση υπάρχοντος κινητήρα
                $motor_id = $motorData['id'];
            } else {
                // Δημιουργία νέου κινητήρα
                $motorQuery = "INSERT INTO motors (customer_id, serial_number, manufacturer, kw, hp, rpm, step, half_step, helper_step, helper_half_step, spiral, half_spiral, helper_spiral, helper_half_spiral, 
                            cross_section, half_cross_section, helper_cross_section, helper_half_cross_section, connectionism, volt, poles, type_of_motor, type_of_volt, type_of_step, created_at) 
                            VALUES (:customer_id, :serial_number, :manufacturer, :kw, :hp, :rpm, :step, :half_step, :helper_step, :helper_half_step, :spiral, :half_spiral, :helper_spiral, :helper_half_spiral,
                            :cross_section, :half_cross_section, :helper_cross_section, :helper_half_cross_section, :connectionism, :volt, :poles, :type_of_motor, :type_of_volt, :type_of_step, :created_at)";
                $motorStmt = $this->conn->prepare($motorQuery);
                $motorStmt->bindParam(':customer_id', $customer_id);
                $motorStmt->bindParam(':serial_number', $motorData['serial_number']);
                $motorStmt->bindParam(':manufacturer', $motorData['manufacturer']);
                $motorStmt->bindParam(':kw', $motorData['kw']);
                $motorStmt->bindParam(':hp', $motorData['hp']);
                $motorStmt->bindParam(':rpm', $motorData['rpm']);
                $motorStmt->bindParam(':step', $motorData['step']);
                $motorStmt->bindParam(':half_step', $motorData['half_step']);
                $motorStmt->bindParam(':helper_step', $motorData['helper_step']);
                $motorStmt->bindParam(':helper_half_step', $motorData['helper_half_step']);
                $motorStmt->bindParam(':spiral', $motorData['spiral']);
                $motorStmt->bindParam(':half_spiral', $motorData['half_spiral']);
                $motorStmt->bindParam(':helper_spiral', $motorData['helper_spiral']);
                $motorStmt->bindParam(':helper_half_spiral', $motorData['helper_half_spiral']);
                $motorStmt->bindParam(':cross_section', $motorData['cross_section']);
                $motorStmt->bindParam(':half_cross_section', $motorData['half_cross_section']);
                $motorStmt->bindParam(':helper_cross_section', $motorData['helper_cross_section']);
                $motorStmt->bindParam(':helper_half_cross_section', $motorData['helper_half_cross_section']);
                $motorStmt->bindParam(':connectionism', $motorData['connectionism']);
                $motorStmt->bindParam(':volt', $motorData['volt']);
                $motorStmt->bindParam(':poles', $motorData['poles']);
                $motorStmt->bindParam(':type_of_motor', $motorData['type_of_motor']);
                $motorStmt->bindParam(':type_of_volt', $motorData['type_of_volt']);
                $motorStmt->bindParam(':type_of_step', $motorData['type_of_step']);
                $motorStmt->bindParam(':created_at', $motorData['created_at']);
                $motorStmt->execute();
                $motor_id = $this->conn->lastInsertId();
            }
            
            // 3. Δημιουργία της επισκευής
            $repairQuery = "INSERT INTO repairs (customer_id, motor_id, description, 
                        repair_status, cost, created_at, is_arrived, estimated_is_complete) 
                        VALUES (:customer_id, :motor_id, :description, :repair_status,
                        :cost, :created_at, :is_arrived, :estimated_is_complete)";
            $repairStmt = $this->conn->prepare($repairQuery);
            $repairStmt->bindParam(':customer_id', $customer_id);
            $repairStmt->bindParam(':motor_id', $motor_id);
            $repairStmt->bindParam(':description', $repairData['description']);
            $repairStmt->bindParam(':repair_status', $repairData['repair_status']);
            $repairStmt->bindParam(':cost', $repairData['cost']);
            $repairStmt->bindParam(':created_at', $repairData['created_at']);
            $repairStmt->bindParam(':is_arrived', $repairData['is_arrived']);
            $repairStmt->bindParam(':estimated_is_complete', $repairData['estimated_is_complete']);
            $repairStmt->execute();
            $repair_id = $this->conn->lastInsertId();

            // 4. Αποθήκευση των τύπων επισκευής (repair types)
            if (isset($common_faults) && is_array($common_faults)) {
                foreach ($common_faults as $common_fault) {
                    $commonFaultQuery = "INSERT INTO repair_fault_links (repair_id, common_fault_id) 
                                    VALUES (:repair_id, :common_fault_id)";
                    $commonFaultStmt = $this->conn->prepare($commonFaultQuery);
                    $commonFaultStmt->bindParam(':repair_id', $repair_id);
                    $commonFaultStmt->bindParam(':common_fault_id', $common_fault);
                    $commonFaultStmt->execute();
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
        
    // public function createCustomer(Customer $customer) {
    //     $query = "INSERT INTO customers (type, name, email, phone, created_at) 
    //             VALUES (:type, :name, :email, :phone, NOW())";
        
    //     $stmt = $this->conn->prepare($query);
        
    //     $name = $customer->name;
    //     $type = $customer->type;
    //     $email = $customer->email;
    //     $phone = $customer->phone;
        
    //     $stmt->bindParam(':type', $type);
    //     $stmt->bindParam(':name', $name);
    //     $stmt->bindParam(':email', $email);
    //     $stmt->bindParam(':phone', $phone);
        
    //     if ($stmt->execute()) {
    //         return $this->conn->lastInsertId();
    //     }
        
    //     return false;
    // }
    
    // public function updateCustomer(Customer $customer) {
    //     $query = "UPDATE customers 
    //             SET type = :type, name = :name, email = :email, phone = :phone 
    //             WHERE id = :id";
        
    //     $stmt = $this->conn->prepare($query);
        
    //     $id = $customer->id;
    //     $type = $customer->type;
    //     $name = $customer->name;
    //     $email = $customer->email;
    //     $phone = $customer->phone;
        
    //     $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
    //     $stmt->bindParam(':type', $type);
    //     $stmt->bindParam(':name', $name);
    //     $stmt->bindParam(':email', $email);
    //     $stmt->bindParam(':phone', $phone);
        
    //     return $stmt->execute();
    // }
    
    // public function deleteCustomer($id) {
    //     $query = "DELETE FROM customers WHERE id = :id";
    //     $stmt = $this->conn->prepare($query);
    //     $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        
    //     return $stmt->execute();
    // }
}