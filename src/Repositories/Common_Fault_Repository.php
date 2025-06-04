<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Common_Fault;

class Common_Fault_Repository
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

        $common_faults = [];
        foreach ($common_faultsData as $common_faultData) {
            $common_faults[] = new Common_Fault($common_faultData);
        }
    }
}
