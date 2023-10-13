<?php
require_once("./vendor/autoload.php");

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

use \Plexcorp\Monitoring\Db;

$db = new Db();
$pdo = $db->getPdo();

try {
    $sql = file_get_contents("./database/install.sql");
    $statement = $pdo->prepare($sql);

    $pdo->beginTransaction();

    $statement->execute();
    $statement->execute();

    $pdo->commit();

    echo "Successfully installed monitoring service sqlitedb.";
} 
catch (\Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    throw $e;
}