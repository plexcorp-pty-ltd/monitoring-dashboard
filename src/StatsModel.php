<?php namespace Plexcorp\Monitoring;

/**
 * $this main model that we use to store and retrieve monitoring statistics.
 */
class StatsModel extends Db
{
    /**
     * Return stat in percentage and timestamp. Can filter by hostname as well.
     *
     * @param string $stat_name
     * @param string $hostname
     *
     * @return array
     */
    public function getPercentagStat($stat_name, $hostname = "all")
    {
        $since = date("Y-m-d H:i:s", strtotime("-30 minutes"));
        $binds = [$since, $stat_name];
        $sql = <<<SQL
            SELECT stat_numerical, TIME(dt_datetime) as dt_time
            FROM server_stats
            WHERE dt_datetime >= ? AND stat_name = ?
        SQL;

        if ($hostname != "all") {
            $sql .= " AND hostname = ?";
            $binds[] = $hostname;
        }

        $sql .= " GROUP BY dt_time ORDER BY dt_time";

        return $this->query($sql, $binds);
    }

    /**
     * Get all hostnames that have submitted a status update in the last 30 minutes.
     *
     * @return array
     */
    public function getHostnames()
    {
        $since = date("Y-m-d H:i:s", strtotime("-30 minutes"));
        $sql = <<<SQL
                SELECT hostname FROM server_stats
                WHERE dt_datetime >= ?
                GROUP BY hostname
            SQL;
        return $this->query($sql, [$since]);
    }

    /**
     * Record a new stat
     *
     * @param array $data
     * @return void
     */
    public function saveStat($data)
    {
        $id = $this->saveData("server_stats", $data);
        $thresholdType = strtoupper($data['stat_name']) . "_THRESHOLD";
        if (isset($_ENV[$thresholdType]) && (float) $data['stat_numerical'] > (float) $_ENV[$thresholdType]) {
            $spikeData = [
                "stat_id" => $id,
                "threshold" => $_ENV[$thresholdType],
                "dt_datetime" => $data['dt_datetime'],
            ];

            $this->saveData("server_spikes", $spikeData);
        }
    }

    /**
     * Will get a list of services that have spiked in within the relevant window period.
     *
     * @param string[datetime] $since
     * @param string $hostname
     * @return array
     */
    public function getSpikes($since, $hostname)
    {
        $sql = <<<SQL
        SELECT s.hostname, s.stat_name,
        count(stat_id) as total_in_alarm,
        MAX(s.stat_numerical) as actual_value,
        MIN(spk.threshold) as threshold
        FROM server_stats s
        JOIN server_spikes spk ON (s.id = spk.stat_id)
        WHERE s.dt_datetime >= ?
        AND s.hostname= ?
        GROUP BY stat_name
        SQL;

        // For some weird reason HAVING in SQlite3 has weird results.
        $spikes = $this->query($sql, [$since, $hostname]);
        foreach($spikes as $id => $spk) {
            if ($spk['total_in_alarm'] < $_ENV["ALERT_THRESHOLD_MIN"]) {
                unset($spikes[$id]);
            }
        }

        return $spikes;
    }

    /**
     * Count how many emails have been sent.
     *
     * @return int
     */
    public function emailsSentToday()
    {
        $sql = "SELECT COUNT(*) as sent FROM emails_sent_today WHERE date_no_time = ?";
        $st = $this->pdo->prepare($sql);
        $st->execute([date("Y-m-d")]);

        return $st->fetchColumn();
    }
}
