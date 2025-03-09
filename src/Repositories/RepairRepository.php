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

// public function getAll() {
//     // Ερώτημα για να πάρεις τα δεδομένα της επισκευής και των πελατών
//     $query = "
//         SELECT repairs.*, customers.*
//         FROM repairs
//         INNER JOIN customers ON repairs.customerID = customers.id
//     ";
    
//     // Εκτέλεση της προετοιμασίας και της εκτέλεσης του query
//     $stmt = $this->conn->prepare($query);
//     $stmt->execute();
    
//     // Ανάκτηση των δεδομένων
//     $repairsData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
//     // Δημιουργία πίνακα για να κρατήσεις τα αντικείμενα Repair
//     $repairs = [];
    
//     // Διασχίζοντας τα δεδομένα και δημιουργώντας το αντικείμενο Repair μαζί με τον Customer
//     foreach ($repairsData as $repairData) {
//         // Δημιουργούμε το αντικείμενο Repair
//         $repair = new Repair($repairData);

//         // Δημιουργία του αντικειμένου Customer
//         $customerData = [
//             'id' => $repairData['customerID'],
//             'type' => $repairData['type'],
//             'name' => $repairData['name'],
//             'email' => $repairData['email'],
//             'phone' => $repairData['phone'],
//             'created_at' => $repairData['created_at'],
//         ];
//         $customer = new Customer($customerData);
        
//         // Ανάθεση του αντικειμένου Customer στην επισκευή
//         $repair->customer = $customer;
        
//         // Προσθήκη της επισκευής στον πίνακα
//         $repairs[] = $repair;
//     }
    
//     // Επιστροφή των αντικειμένων Repair
//     return $repairs;
// }
public function getAll() {
    $query = "
        SELECT repairs.*, 
               customers.id AS customer_id, customers.type, customers.name, customers.email, customers.phone, customers.created_at AS customer_created_at,
               motors.id AS motor_id, motors.serial_number, motors.manufacturer, motors.kw, motors.hp, motors.rpm, motors.step, motors.spiral, motors.cross_section, motors.connectionism, motors.volt, motors.poles, motors.created_at AS motor_created_at
        FROM repairs
        INNER JOIN customers ON repairs.customerID = customers.id
        INNER JOIN motors ON repairs.motorID = motors.id
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
            'spiral' => $repairData['spiral'],
            'cross_section' => $repairData['cross_section'],
            'connectionism' => $repairData['connectionism'],
            'volt' => $repairData['volt'],
            'poles' => $repairData['poles'],
            'created_at' => $repairData['created_at'],
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