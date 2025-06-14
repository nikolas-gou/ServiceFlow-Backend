<?php

namespace App\Config;

class Database
{
    private $host = '127.0.0.1';
    private $db_name = 'motor_service';
    private $username = 'root';
    private $password = '';

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $db = $config['db'];

        $this->host = $db['host'];
        $this->db_name = $db['name'];
        $this->username = $db['user'];
        $this->password = $db['pass'];
    }

    public function getConnection()
    {
        try {
            $conn = new \PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (\PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}
