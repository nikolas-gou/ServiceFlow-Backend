<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Customer;

class CustomerRepository
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

    // future functions 
    public function getCustomerById($id)
    {
        $query = "SELECT * FROM customers WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $customerData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$customerData) {
            return null;
        }

        return new Customer($customerData);
    }

    public function createCustomer(Customer $customer)
    {
        $query = "INSERT INTO customers (type, name, email, phone, created_at) 
                VALUES (:type, :name, :email, :phone, NOW())";

        $stmt = $this->conn->prepare($query);

        $name = $customer->name;
        $type = $customer->type;
        $email = $customer->email;
        $phone = $customer->phone;

        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function updateCustomer(Customer $customer)
    {
        $query = "UPDATE customers 
                SET type = :type, name = :name, email = :email, phone = :phone 
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $id = $customer->id;
        $type = $customer->type;
        $name = $customer->name;
        $email = $customer->email;
        $phone = $customer->phone;

        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);

        return $stmt->execute();
    }

    public function deleteCustomer($id)
    {
        $query = "DELETE FROM customers WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

        return $stmt->execute();
    }
}
