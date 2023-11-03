<?php namespace Plexcorp\Monitoring;

/**
 * A simple wrapper around PDO to make querying and saving data easier.
 */
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

    /**
     * Will perform a SQL insert.
     *
     * @param string $table
     * @param array $data
     * 
     * @return int
     */
    public function saveData($table, $data) :string|false|int
    {
        $placeholders = trim(str_repeat(",?", count($data)), ",");
        $cols = implode(",", array_keys($data));

        $sql = "INSERT INTO ". $table . "($cols) VALUES($placeholders)";
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute(array_values($data));

        return $this->pdo->lastInsertId();
    }

    /**
     * Will run "SELECT ..." queries
     *
     * @param string $sql
     * @param array $params
     * 
     * @return array
     */
    public function query($sql, $params)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Fetch a single row from the database
     *
     * @param string $sql
     * @param array $params
     * 
     * @return array
     */
    public function fetchOne($sql, $params)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchObject();
    }


    /**
     * Similar to above except this one is for UPDATE OR DELETE statements.
     *
     * @param string $sql
     * @param array $params
     * @return void
     */
    public function queryNoResult($sql, $params)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
}