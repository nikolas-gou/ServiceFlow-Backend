<?php

namespace App\Repositories;

use App\Models\CommonFault;
use PDO;

class CommonFaultRepository
{
    private $conn;

    public function __construct(PDO $pdo)
    {
        $this->conn = $pdo;
    }

    public function getAll(): array
    {
        $query = "SELECT * FROM common_faults ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $commonFaultsData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $commonFaults = [];
        foreach ($commonFaultsData as $commonFaultData) {
            $commonFault = new CommonFault($commonFaultData);
            $commonFaults[] = $commonFault->toFrontendFormat();
        }
        
        return $commonFaults;
    }
}
