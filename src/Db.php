<?php namespace Plexcorp\Monitoring;

class Db {
    
    protected $pdo;

    public function __construct()
    {
        $this->pdo = new \PDO($_ENV['DB_DSN']);
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    public function __destruct()
    {
        $this->pdo = null;
    }

    public function saveData($table, $data)
    {
        $placeholders = trim(str_repeat(",?", count($data)), ",");
        $cols = implode(",", array_keys($data));

        $sql = "INSERT INTO ". $table . "($cols) VALUES($placeholders)";
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute(array_values($data));

        return $this->pdo->lastInsertId();
    }

    public function query($sql, $params)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function queryNoResult($sql, $params)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
}