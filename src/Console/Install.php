<?php
namespace Plexcorp\Monitoring\Console;

use PDO;
use Plexcorp\Monitoring\Db;

class Install
{
    private array $args;
    private PDO $pdo;
    public function __construct(array $args)
    {
        $this->args = $args;
    }

    public function run()
    {
        if (file_exists("./statsdb.db")) {
            throw new \Exception("Refusing to run. Stats DB already exists. Please delete this first.");
        }

        $this->pdo = (new Db())->getPdo();

        try {
            $serverStatsQuery = $this->pdo->prepare(
                "
                CREATE TABLE IF NOT EXISTS server_stats(
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    hostname VARCHAR(100),
                    stat_name VARCHAR(20),
                    stat_numerical decimal(18, 2),
                    stat_data TEXT,
                    dt_datetime datetime
                )
                "
            );

            $serverSpikesQuery = $this->pdo->prepare(
                "
                    CREATE TABLE IF NOT EXISTS server_spikes(
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        stat_id INTEGER NOT NULL,
                        threshold decimal(18,2) NOT NULL,
                        dt_datetime datetime
                    )
                "
            );

            $emailsSent = $this->pdo->prepare(
                "
                    CREATE TABLE IF NOT EXISTS emails_sent_today(
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        date_no_time date
                    )
                "
            );

            $users = $this->pdo->prepare(
                "
                    CREATE TABLE IF NOT EXISTS users(
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        username VARCHAR(255) NOT NULL,
                        password VARCHAR(100) NOT NULL,
                        enabled tinyint(1) NOT NULL
                    )
                "
            );

            $failedLogins = $this->pdo->prepare(
                "
                    CREATE TABLE IF NOT EXISTS failed_logins(
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        ip_address VARCHAR(50) NOT NULL,
                        last_attempt datetime timestamp
                    )
                "
            );

            $this->pdo->beginTransaction();
            $serverStatsQuery->execute();
            $serverSpikesQuery->execute();
            $emailsSent->execute();
            $users->execute();
            $failedLogins->execute();

            $this->pdo->commit();

            echo "Successfully installed monitoring service sqlitedb." . PHP_EOL;
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollback();
            }
            throw $e;
        }
    }
}
