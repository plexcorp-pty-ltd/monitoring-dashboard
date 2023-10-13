<?php
require_once "./vendor/autoload.php";
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
use \Plexcorp\Monitoring\Stats;

$hostName = gethostname();
$datetime = date("Y-m-d H:i:s");
$model = null;

try {
    $stats = [
        [
            "hostname" => $hostName,
            "stat_name" => "cpu",
            "stat_numerical" => Stats::CpuUsuage(),
            "dt_datetime" => $datetime,
            "stat_data" => "",
        ],

        [
            "hostname" => $hostName,
            "stat_name" => "memory",
            "stat_numerical" => Stats::MemoryUsage(),
            "dt_datetime" => $datetime,
            "stat_data" => "",
        ],

        [
            "hostname" => $hostName,
            "stat_name" => "diskspace",
            "stat_numerical" => Stats::DiskUsage(),
            "dt_datetime" => $datetime,
            "stat_data" => "",
        ],

        [
            "hostname" => $hostName,
            "stat_name" => "number_running_processes",
            "stat_numerical" => Stats::numberOfRunningProcesses(),
            "dt_datetime" => $datetime,
            "stat_data" => "",
        ],
    ];

    if ($_ENV['MASTER_NODE'] != 'yes') {
        echo "Running as regular node. Will post to the master nodes API" . PHP_EOL;
        foreach ($stats as $stat) {
            Stats::saveViaApi($stat);
        }
    } else {
        echo "Running in master mode. Will connect directly to master db." . PHP_EOL;
        $model = new \Plexcorp\Monitoring\StatsModel();
        foreach ($stats as $stat) {
            $model->saveData("server_stats", $stat);
        }
    }

} catch (\Exception $e) {
    print $e->getMessage();
}

if ($_ENV['MASTER_NODE'] == 'yes') {
    echo "Running cleaup for old logs..." . PHP_EOL;
    $hours = (int)$_ENV['HOURS_TO_KEEP_LOGS'];
    if ($model == null) {
        $model = new \Plexcorp\Monitoring\StatsModel();
    }
    $model->queryNoResult("DELETE FROM server_stats WHERE dt_datetime < ?", [
        date("Y-m-d H:i:s", strtotime("-{$hours} hour"))
    ]);
}