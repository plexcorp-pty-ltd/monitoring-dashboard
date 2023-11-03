<?php
/**
 * Creates all the SQL tables needed for the project.
 * Please delete this file after running for the first time.
 */
require_once("../vendor/autoload.php");

use Dotenv\Dotenv;
if(file_exists("../.env")) {
    $dotenv = Dotenv::createImmutable("..".__DIR__);
    $dotenv->load();    
}

use \Plexcorp\Monitoring\Db;

$db = new Db();
$pdo = $db->getPdo();

try {
    $serverStatsQuery = $pdo->prepare(
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

    $serverSpikesQuery = $pdo->prepare(
        "
         CREATE TABLE IF NOT EXISTS server_spikes(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            stat_id INTEGER NOT NULL,
            threshold decimal(18,2) NOT NULL,
            dt_datetime datetime
         )
        "
    );

    $emailsSent = $pdo->prepare(
        "
         CREATE TABLE IF NOT EXISTS emails_sent_today(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            date_no_time date
         )
        "
    );

    $users = $pdo->prepare(
        "
         CREATE TABLE IF NOT EXISTS users(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(255) NOT NULL,
            password VARCHAR(100) NOT NULL,
            enabled tinyint(1) NOT NULL
         )
        "
    );

    $apikeys = $pdo->prepare(
        "
         CREATE TABLE IF NOT EXISTS api_keys(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            apikey VARCHAR(255) NOT NULL,
            enabled tinyint(1) NOT NULL
         )
        "
    );

    $failedLogins = $pdo->prepare(
        "
         CREATE TABLE IF NOT EXISTS failed_logins(
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address VARCHAR(50) NOT NULL,
            last_attempt datetime timestamp
         )
        "
    );

    $pdo->beginTransaction();
    $serverStatsQuery->execute();
    $serverSpikesQuery->execute();
    $emailsSent->execute();
    $users->execute();
    $apikeys->execute();
    $pdo->commit();

    echo "Successfully installed monitoring service sqlitedb.";
} 
catch (\Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    throw $e;
}