<?php
namespace App\Repositories;

use App\Config\Database;
use App\Models\Customer;

class CustomerRepository {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // convert to getAll in a little bit
    public function getAll() {
        $query = "SELECT * FROM customers";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $customers = [];
        foreach ($customersData as $customerData) {
            $customers[] = new Customer($customerData);
        }
        
        return $customers;
    }

    public function getCustomerById($id) {
        $query = "SELECT * FROM customers WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}