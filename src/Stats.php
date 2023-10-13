<?php

namespace Plexcorp\Monitoring;

class Stats 
{
    static function saveViaApi($data) {
        $data['SHELL_API_KEY'] = $_ENV['SHELL_API_KEY'];
        $url = $_ENV['STAT_API_URL'] . "?action=savestats";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error !== '') {
            throw new \Exception($error);
        }
    
        return $response;
    }

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

    static function numberOfRunningProcesses() 
    {
        $cmd = 'ps -e | wc -l';
        return shell_exec($cmd);
    }
}