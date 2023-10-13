<?php namespace Plexcorp\Monitoring;

class StatsModel extends Db 
{
    function getPercentagStat($stat_name, $hostname = "all")
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

    function getHostnames()
    {
        $since = date("Y-m-d H:i:s", strtotime("-30 minutes"));
        $sql = <<<SQL
                SELECT hostname FROM server_stats
                WHERE dt_datetime >= ?
                GROUP BY hostname
            SQL;
        return $this->query($sql, [$since]);
    }
}