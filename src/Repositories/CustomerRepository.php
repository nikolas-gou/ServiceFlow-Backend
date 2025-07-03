<?php

namespace App\Repositories;

use App\Models\Customer;
use PDO;

class CustomerRepository
{
    private $conn;

    public function __construct(PDO $pdo)
    {
        $this->conn = $pdo;
    }

    public function getAll(): array
    {
        $query = "SELECT * FROM customers ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $customersData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $customers = [];
        foreach ($customersData as $customerData) {
            $customer = new Customer($customerData);
            $customers[] = $customer->toFrontendFormat();
        }

        return $customers;
    }

    public function getCustomerById($id): ?array
    {
        $query = "SELECT * FROM customers WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $customerData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$customerData) {
            return null;
        }

        $customer = new Customer($customerData);
        return $customer->toFrontendFormat();
    }

    public function createCustomer(Customer $customer): int|false
    {
        $query = "INSERT INTO customers (type, name, email, phone, created_at) 
                VALUES (:type, :name, :email, :phone, NOW())";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':type', $customer->type);
        $stmt->bindParam(':name', $customer->name);
        $stmt->bindParam(':email', $customer->email);
        $stmt->bindParam(':phone', $customer->phone);

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
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

    /**
     * Πελάτες ανά μήνα ανά κατηγορία
     */
    public function getCustomersByTypeAndMonth(): array
    {
        $currentYear = date('Y');
        $currentMonth = (int) date('m');
        
        $stmt = $this->conn->prepare("
            SELECT 
                type,
                MONTH(created_at) as month,
                COUNT(*) as count
            FROM customers 
            WHERE YEAR(created_at) = ? 
            AND MONTH(created_at) <= ?
            GROUP BY type, MONTH(created_at)
            ORDER BY type, month ASC
        ");
        
        $stmt->execute([$currentYear, $currentMonth]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Δημιουργία structured array
        $monthlyData = [
            'individual' => array_fill(0, $currentMonth, 0),
            'factory' => array_fill(0, $currentMonth, 0)
        ];
        
        foreach ($results as $row) {
            $type = $row['type'];
            $monthIndex = (int)$row['month'] - 1;
            if (isset($monthlyData[$type])) {
                $monthlyData[$type][$monthIndex] = (int)$row['count'];
            }
        }
        
        return $monthlyData;
    }

    /**
     * Καλύτερος πελάτης με βάση τα έσοδα
     */
    public function getTopCustomerByRevenue(int $limit = 5): array
    {
        $stmt = $this->conn->prepare("
            SELECT 
                c.id,
                c.name,
                c.type,
                c.email,
                c.phone,
                COUNT(r.id) as totalRepairs,
                COALESCE(SUM(r.cost), 0) as totalRevenue
            FROM customers c
            LEFT JOIN repairs r ON c.id = r.customer_id
            GROUP BY c.id, c.name, c.type, c.email, c.phone
            ORDER BY totalRevenue DESC
            LIMIT " . (int)$limit
        );
        
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Στατιστικά ανά κατηγορία πελατών
     */
    public function getCustomerTypeStats(): array
    {
        $stmt = $this->conn->prepare("
            SELECT 
                type,
                COUNT(*) as totalCount,
                COUNT(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) THEN 1 END) as thisYearCount,
                COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 END) as thisMonthCount
            FROM customers 
            GROUP BY type
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Πελάτες ανά μήνα με περισσότερες λεπτομέρειες
     */
    public function getDetailedMonthlyStats(): array
    {
        $currentYear = date('Y');
        $currentMonth = (int) date('m');
        
        $stmt = $this->conn->prepare("
            SELECT 
                MONTH(created_at) as month,
                COUNT(*) as totalCustomers,
                COUNT(CASE WHEN type = 'individual' THEN 1 END) as individualCustomers,
                COUNT(CASE WHEN type = 'factory' THEN 1 END) as factoryCustomers
            FROM customers 
            WHERE YEAR(created_at) = ? 
            AND MONTH(created_at) <= ?
            GROUP BY MONTH(created_at)
            ORDER BY month ASC
        ");
        
        $stmt->execute([$currentYear, $currentMonth]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Δημιουργία array με όλους τους μήνες
        $monthlyData = [];
        for ($month = 1; $month <= $currentMonth; $month++) {
            $monthlyData[] = [
                'month' => $month,
                'totalCustomers' => 0,
                'individualCustomers' => 0,
                'factoryCustomers' => 0
            ];
        }
        
        // Συμπλήρωση με πραγματικά δεδομένα
        foreach ($results as $row) {
            $monthIndex = (int)$row['month'] - 1;
            if (isset($monthlyData[$monthIndex])) {
                $monthlyData[$monthIndex] = [
                    'month' => (int)$row['month'],
                    'totalCustomers' => (int)$row['totalCustomers'],
                    'individualCustomers' => (int)$row['individualCustomers'],
                    'factoryCustomers' => (int)$row['factoryCustomers']
                ];
            }
        }
        
        return $monthlyData;
    }

    /**
     * Πελάτες ανά μήνα ανά κατηγορία
     */
    public function getCountByTypeAndMonth(string $type, string $monthKey): int
    {
        // $monthKey format: '2025-01'
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM customers 
            WHERE type = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?
        ");
        $stmt->execute([$type, $monthKey]);
        return (int) $stmt->fetchColumn();
    }

    public function checkAndUpdateCustomerDetails(Customer $newCustomerData): bool
    {
        // Get existing customer data
        $existingCustomer = $this->getCustomerById($newCustomerData->id);
        if (!$existingCustomer) {
            return false;
        }

        // Check if any details have changed
        $hasChanges = false;
        if ($existingCustomer['email'] !== $newCustomerData->email ||
            $existingCustomer['phone'] !== $newCustomerData->phone ||
            $existingCustomer['type'] !== $newCustomerData->type ||
            $existingCustomer['name'] !== $newCustomerData->name) {
            $hasChanges = true;
        }

        // If there are changes, update the customer
        if ($hasChanges) {
            return $this->updateCustomer($newCustomerData);
        }

        return true;
    }

}
