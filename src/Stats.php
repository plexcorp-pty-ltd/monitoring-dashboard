<?php

namespace Plexcorp\Monitoring;

class Stats 
{
    static function CpuUsuage() 
    {
        $avgs = sys_getloadavg();
        $cores = (int) shell_exec("grep -c ^processor /proc/cpuinfo");
        
        return round($avgs[1] / $cores * 100, 2);
    }

    static function MemoryUsage()
    {
        $cmd = 'free | grep Mem | awk \'{print $3/$2 * 100.0}\'';
        return shell_exec($cmd);
    }

    static function DiskUsage()
    {
        $cmd = "df -h --total / | grep total | awk '{print $5}'";
        return str_replace("%", "", shell_exec($cmd));
    }
    
    static function runningProcesses()
    {
        $cmd = "ps auxr --sort -rss | awk '{print $2\",\"$3\",\"$4\",\"$10\",\"$11}'";
        $result = shell_exec($cmd);

        $result = explode("\n",$result);

        return $result;
    }
}