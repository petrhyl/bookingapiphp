<?php

class Database
{
    private $conn;

    public function __construct(
        private string $host,
        private string $db_name,
        private string $username,
        private string $password
    ) {
    }

    public function getConnection()
    {
        $this->conn = null;

        $this->conn = new PDO(
            'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
            $this->username,
            $this->password
        );
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);       

        return $this->conn;
    }
}
