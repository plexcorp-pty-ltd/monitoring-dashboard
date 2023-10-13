<?php
require_once("./vendor/autoload.php");
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

use \Plexcorp\Monitoring\Stats;
use \Plexcorp\Monitoring\Db;

$db = new Db();
$hostName = gethostname();
$datetime = date("Y-m-d H:i:s");

try {
    $db->saveData("server_stats", [
        "hostname" => $hostName,
        "stat_name" => "cpu",
        "stat_percentage" => Stats::CpuUsuage(),
        "dt_datetime" => $datetime,
        "stat_data" => ""
    ]);
    
    $db->saveData("server_stats", [
        "hostname" => $hostName,
        "stat_name" => "memory",
        "stat_percentage" => Stats::MemoryUsage(),
        "dt_datetime" => $datetime,
        "stat_data" => ""
    ]);
    
    $db->saveData("server_stats", [
        "hostname" => $hostName,
        "stat_name" => "diskspace",
        "stat_percentage" => Stats::DiskUsage(),
        "dt_datetime" => $datetime,
        "stat_data" => ""
    ]);
    
    $db->saveData("server_stats", [
        "hostname" => $hostName,
        "stat_name" => "processes",
        "stat_percentage" => '',
        "dt_datetime" => $datetime,
        "stat_data" => serialize(Stats::runningProcesses())
    ]);
} catch (\Exception $e) {
    print $e->getMessage();
}

$hours = (int)$_ENV['HOURS_TO_KEEP_LOGS'];

print "Clearing logs older than {$hours} hours." . PHP_EOL;

$db->queryNoResult("DELETE FROM server_stats WHERE dt_datetime < ?", [
    date("Y-m-d H:i:s", strtotime("-{$hours} hour"))
]);