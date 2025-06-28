<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\CommonFault;

class CommonFaultRepository
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll()
    {
        $query = "SELECT * FROM common_faults";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $commonFaults = [];
        foreach ($commonFaultsData as $commonFaultData) {
            $commonFaults[] = new CommonFault($commonFaultData);
        }
    }
}
