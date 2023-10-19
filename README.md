# Monitoring dashboard

This project is built using PHP, and is designed to be a basic monitoring dashboard for Linux servers - similar to the "htop" command. Using a CRON - you can collect metrics on any Linux server periodically and post to the dashboard.

The dashboard will then refresh every so often, providing a near real time health check for your servers. There is also a email alert facility to notify you of abnormal spikes.


# What metrics are collected?

Currently core metrics such as:

 1. CPU utilisation.
 2. Memory utilisation.
 3. Disk space usage.
 4. Running processes.

## Documentation
You can find full documentation here and as well as comments in the source code:
https://scriptables.gitbook.io/monitoring-dashboard/
