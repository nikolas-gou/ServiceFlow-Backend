<?php
namespace App\Repositories;

use App\Config\Database;

class StatsOfCustomerRepository {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllStats() {
        $query = "SELECT * FROM customers";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTotalCustomers() {
        $query = "SELECT COUNT(*) as total FROM customers";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getCustomersPerMonth() {
        $query = "
            SELECT MONTH(created_at) - 1 AS month, COUNT(*) AS total 
            FROM customers 
            WHERE YEAR(created_at) = YEAR(CURDATE()) 
            GROUP BY MONTH(created_at)
            ORDER BY MONTH(created_at)
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Δημιουργούμε έναν πίνακα με 12 θέσεις (0 = Ιανουάριος, 1 = Φεβρουάριος, ...)
        $months = array_fill(0, 12, 0);

        // Συμπληρώνουμε τον πίνακα με τα αποτελέσματα από τη βάση
        foreach ($results as $row) {
            $months[$row['month']] = $row['total'];
        }

        return $months;
    }

    public function getTotalCustomersByType() {
    $query = "SELECT type, COUNT(*) as total FROM customers GROUP BY type";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }
}