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
               motors.halfStep,
               motors.helperStep,
               motors.helperHalfStep,
               motors.spiral, 
               motors.halfSpiral,
               motors.helperSpiral,
               motors.helperHalfSpiral,
               motors.cross_section, 
               motors.halfCross_section,
               motors.helperCross_section,
               motors.helperHalfCross_section,
               motors.connectionism, 
               motors.volt, 
               motors.poles,
               motors.typeOfStep,
               motors.typeOfMotor,
               motors.typeOfVolt,
               motors.created_at AS motor_created_at,
               motors.customerID AS motor_customerID
        FROM repairs
        INNER JOIN customers ON repairs.customerID = customers.id
        INNER JOIN motors ON repairs.motorID = motors.id
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
            'halfStep' => $repairData['halfStep'],
            'helperStep' => $repairData['helperStep'],
            'helperHalfStep' => $repairData['helperHalfStep'],
            'spiral' => $repairData['spiral'],
            'halfSpiral' => $repairData['halfSpiral'],
            'helperSpiral' => $repairData['helperSpiral'],
            'helperHalfSpiral' => $repairData['helperHalfSpiral'],
            'cross_section' => $repairData['cross_section'],
            'halfCross_section' => $repairData['halfCross_section'],
            'helperCross_section' => $repairData['helperCross_section'],
            'helperHalfCross_section' => $repairData['helperHalfCross_section'],
            'connectionism' => $repairData['connectionism'],
            'volt' => $repairData['volt'],
            'poles' => $repairData['poles'],
            'typeOfStep' => $repairData['typeOfStep'],
            'typeOfMotor' => $repairData['typeOfMotor'],
            'typeOfVolt' => $repairData['typeOfVolt'],
            'created_at' => $repairData['motor_created_at'],
            'customerID' => $repairData['motor_customerID'],
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
    
    public function createNewRepair($repairData, $customerData, $motorData, $repair_types) {
        try {
            $this->conn->beginTransaction();
            
            // 1. Έλεγχος αν ο πελάτης υπάρχει ήδη ή δημιουργία νέου πελάτη
            $customerId = null;
            if (isset($customerData['id']) && $customerData['id'] > 0) {
                // Χρήση υπάρχοντος πελάτη
                $customerId = $customerData['id'];
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
                $customerId = $this->conn->lastInsertId();
            }
            
            // 2. Έλεγχος αν ο κινητήρας υπάρχει ήδη ή δημιουργία νέου κινητήρα
            // Σχεδον παντα δεν θα υπαρχει ιδιος
            $motorId = null;
            if (isset($motorData['id']) && $motorData['id'] > 0) {
                // Χρήση υπάρχοντος κινητήρα
                $motorId = $motorData['id'];
            } else {
                // Δημιουργία νέου κινητήρα
                $motorQuery = "INSERT INTO motors (customerID, serial_number, manufacturer, kw, hp, rpm, step, halfStep, helperStep, helperHalfStep, spiral, halfSpiral, helperSpiral, helperHalfSpiral, 
                            cross_section, halfCross_section, helperCross_section, helperHalfCross_section, connectionism, volt, poles, typeOfMotor, typeOfVolt, typeOfStep, created_at) 
                            VALUES (:customerID, :serial_number, :manufacturer, :kw, :hp, :rpm, :step, :halfStep, :helperStep, :helperHalfStep, :spiral, :halfSpiral, :helperSpiral, :helperHalfSpiral,
                            :cross_section, :halfCross_section, :helperCross_section, :helperHalfCross_section, :connectionism, :volt, :poles, :typeOfMotor, :typeOfVolt, :typeOfStep, :created_at)";
                $motorStmt = $this->conn->prepare($motorQuery);
                $motorStmt->bindParam(':customerID', $customerId);
                $motorStmt->bindParam(':serial_number', $motorData['serial_number']);
                $motorStmt->bindParam(':manufacturer', $motorData['manufacturer']);
                $motorStmt->bindParam(':kw', $motorData['kw']);
                $motorStmt->bindParam(':hp', $motorData['hp']);
                $motorStmt->bindParam(':rpm', $motorData['rpm']);
                $motorStmt->bindParam(':step', $motorData['step']);
                $motorStmt->bindParam(':halfStep', $motorData['halfStep']);
                $motorStmt->bindParam(':helperStep', $motorData['helperStep']);
                $motorStmt->bindParam(':helperHalfStep', $motorData['helperHalfStep']);
                $motorStmt->bindParam(':spiral', $motorData['spiral']);
                $motorStmt->bindParam(':halfSpiral', $motorData['halfSpiral']);
                $motorStmt->bindParam(':helperSpiral', $motorData['helperSpiral']);
                $motorStmt->bindParam(':helperHalfSpiral', $motorData['helperHalfSpiral']);
                $motorStmt->bindParam(':cross_section', $motorData['cross_section']);
                $motorStmt->bindParam(':halfCross_section', $motorData['halfCross_section']);
                $motorStmt->bindParam(':helperCross_section', $motorData['helperCross_section']);
                $motorStmt->bindParam(':helperHalfCross_section', $motorData['helperHalfCross_section']);
                $motorStmt->bindParam(':connectionism', $motorData['connectionism']);
                $motorStmt->bindParam(':volt', $motorData['volt']);
                $motorStmt->bindParam(':poles', $motorData['poles']);
                $motorStmt->bindParam(':typeOfMotor', $motorData['typeOfMotor']);
                $motorStmt->bindParam(':typeOfVolt', $motorData['typeOfVolt']);
                $motorStmt->bindParam(':typeOfStep', $motorData['typeOfStep']);
                $motorStmt->bindParam(':created_at', $motorData['created_at']);
                $motorStmt->execute();
                $motorId = $this->conn->lastInsertId();
            }
            
            // 3. Δημιουργία της επισκευής
            $repairQuery = "INSERT INTO repairs (customerID, motorID, description, 
                        repair_status, cost, created_at, isArrived, estimatedIsComplete) 
                        VALUES (:customerID, :motorID, :description, :repair_status,
                        :cost, :created_at, :isArrived, :estimatedIsComplete)";
            $repairStmt = $this->conn->prepare($repairQuery);
            $repairStmt->bindParam(':customerID', $customerId);
            $repairStmt->bindParam(':motorID', $motorId);
            $repairStmt->bindParam(':description', $repairData['description']);
            $repairStmt->bindParam(':repair_status', $repairData['repair_status']);
            $repairStmt->bindParam(':cost', $repairData['cost']);
            $repairStmt->bindParam(':created_at', $repairData['created_at']);
            $repairStmt->bindParam(':isArrived', $repairData['isArrived']);
            $repairStmt->bindParam(':estimatedIsComplete', $repairData['estimatedIsComplete']);
            $repairStmt->execute();
            $repairId = $this->conn->lastInsertId();

            // 4. Αποθήκευση των τύπων επισκευής (repair types)
            if (isset($repair_types) && is_array($repair_types)) {
                foreach ($repair_types as $repairType) {
                    $repairItemQuery = "INSERT INTO repair_items (repairID, repair_typeID) 
                                    VALUES (:repairID, :repair_typeID)";
                    $repairItemStmt = $this->conn->prepare($repairItemQuery);
                    $repairItemStmt->bindParam(':repairID', $repairId);
                    $repairItemStmt->bindParam(':repair_typeID', $repairType);
                    $repairItemStmt->execute();
                }
            }
            
            $this->conn->commit();
            
            // 4. Ανάκτηση πλήρους επισκευής με πελάτη και κινητήρα
            return $this->getRepairById($repairId);
            
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