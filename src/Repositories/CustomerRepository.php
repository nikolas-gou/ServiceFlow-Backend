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

        $stmt->bindParam(':type', $customer->type);
        $stmt->bindParam(':name', $customer->name);
        $stmt->bindParam(':email', $customer->email);
        $stmt->bindParam(':phone', $customer->phone);

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

    // Statistics 
    public function getTotalCount(): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM customers");
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
            FROM customers 
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
    public function getCountByType(string $type): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM customers WHERE type = ?");
        $stmt->execute([$type]);
        return (int) $stmt->fetchColumn();
    }

    public function getCountByMonth(string $monthKey): int
    {
        // $monthKey format: '2025-01'
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM customers 
            WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
        ");
        $stmt->execute([$monthKey]);
        return (int) $stmt->fetchColumn();
    }

}
