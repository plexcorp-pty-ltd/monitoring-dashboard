<?php 
namespace Plexcorp\Monitoring\Console;
use \Plexcorp\Monitoring\Stats;
use \Plexcorp\Monitoring\StatsModel;
use \Plexcorp\Monitoring\Mailer;

/**
 * In the .env file you will see a variable:
 * MASTER_NODE=yes
 * 
 * If you would like to monitor multiple servers. Simply set MASTER_NODE=no on each additional server.
 * The main server which runs the dashboard must have this setting
 * set to "yes".
 * 
 * It's not required but ideally run this agent.php as root. A none root user may not have full access to count all processes therefore
 * the total processes report will not be 100% accurate if running as a none root user.
 * 
 * Ensure that you set MASTER_NODE=no for the additional servers. This will then make the agent POST to the master server.
 * 
 * MASTER_NODE=yes will do the following:
 * 
 * 1) Collect stats as with other nodes. Except, the master writes to the DB directly instead of posting to the API.
 * 2) Will run cleanup tasks like deleting old logs. You can control how long to keep logs for with the env: HOURS_TO_KEEP_LOGS
 * 3) Will send email alerts if any server has reached a threshold limit. See env's below:
 * 
 * 3.1) CPU_THRESHOLD,MEMORY_THRESHOLD,DISKSPACE_THRESHOLD - controls the threshold for each type. Must be a float percentage value. e.g. 90, 80.50
 * 3.2) MAX_THRESHOLD_EMAILS_PER_DAY - maximum number of emails that can be sent per day.
 * 3.3) THRESHOLD_SCAN_PERIOD - does a lookup for spikes that occured in the last x minutes. 
 * 3.4) ALERT_THRESHOLD_MIN - the number of times a threshold must be hit in the above period before an email can be triggered.
 * 
 */

class Agent
{
    private StatsModel $model;
    private array $args;

    public function __construct(array $args) 
    {
        $this->model = new StatsModel();
        $this->args = $args;
    }

    private function getStats() :array 
    {
        $hostName = gethostname();
        $datetime = date("Y-m-d H:i:s");

        return [
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
    }

    function runCleanupTasks()
    {
        try {
        
            echo "Running cleaup for old logs..." . PHP_EOL;
            $hours = (int)$_ENV['HOURS_TO_KEEP_LOGS'];

            $clearWindow = date("Y-m-d H:i:s", strtotime("-{$hours} hour"));
    
            $this->model->queryNoResult("DELETE FROM server_stats WHERE dt_datetime < ?", [$clearWindow]);
            $this->model->queryNoResult("DELETE FROM server_spikes WHERE dt_datetime < ?", [$clearWindow]);
            $this->model->queryNoResult("DELETE FROM emails_sent_today WHERE date_no_time != ?", [date("Y-m-d")]);
        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    function sendAlarms() 
    {

        $emailsSentToday = $this->model->emailsSentToday();
        if ($emailsSentToday < (int) $_ENV['MAX_THRESHOLD_EMAILS_PER_DAY']) {
            $hosts = $this->model->getHostnames();
            $since = date("Y-m-d H:i:s", strtotime("-".$_ENV['THRESHOLD_SCAN_PERIOD']." minutes"));
    
            echo "Now scanning for spikes since {$since}..." . PHP_EOL;
    
            $alarms = [];
    
            foreach($hosts as $host) {
                try {
                    $spikes = $this->model->getSpikes($since, $host['hostname']);
                    $alarms = array_merge($alarms, $spikes);
                } catch(\Exception $e) {
                    print $e->getMessage();
                }
            }
    
            if (count($alarms) > 0) {
                echo "Sending email alerts...";

                // Record this as a send.
                $this->model->saveData("emails_sent_today", ["date_no_time" => date("Y-m-d")]);
    
                $mailer = new Mailer();
                $vars = [
                    "thresholds" => $alarms,
                ];
                $mailer->sendEmail("threshold_limit_reached", "Monitoring Dashboard - Server Threshold Alert", $vars);
            }
        }
    
    }

    function run() 
    {
        try {
            $stats = $this->getStats();

            if ($_ENV['MASTER_NODE'] != 'yes') {
                echo "Running as regular node. Will post to the master nodes API" . PHP_EOL;
                foreach ($stats as $stat) {
                    Stats::saveViaApi($stat);
                }
            } else {
                echo "Running in master mode. Will connect directly to master db." . PHP_EOL;
                foreach ($stats as $stat) {
                    $this->model->saveStat($stat);
                }
            }
        
        } catch (\Exception $e) {
            print $e->getMessage();
        }
        
        if ($_ENV['MASTER_NODE'] == 'yes') {
            $this->runCleanupTasks();
        }

        // Please ensure all SMTP_ settings in the .env is set correctly, otherwise emails will fail.
        if ($_ENV['SMTP_HOST'] != null && $_ENV['SMTP_HOST'] != "null" && !empty($_ENV['SMTP_HOST'])) {
            $this->sendAlarms();
        }
    }
}