<?php

// IN PROD - DELETE THIS FILE. use ENV's instead.

putenv("DB_DSN=sqlite:./statsdb.db");
putenv("HOURS_TO_KEEP_LOGS=6");
putenv("STAT_API_URL=https://monitoringdash.plexscriptables.com");
putenv("MASTER_NODE=yes");
putenv("SMTP_HOST=sandbox.smtp.mailtrap.io");
putenv("SMTP_PORT=2525");
putenv("SMTP_USERNAME=xxxx");
putenv("SMTP_PASSWORD=xxxx");
putenv("SMTP_TLS=tls");
putenv("SMTP_DEFAULT_FROM=noreply@test.com");
putenv("SMTP_REPORT_EMAILS=test@test.com,test2@test.com");
putenv("CPU_THRESHOLD=90");
putenv("MEMORY_THRESHOLD=0");
putenv("DISKSPACE_THRESHOLD=90");
putenv("MAX_THRESHOLD_EMAILS_PER_DAY=3");
putenv("THRESHOLD_SCAN_PERIOD=5");
putenv("ALERT_THRESHOLD_MIN=3");
putenv("SHELL_API_KEY=XDiop23290!(XDiop23290592323&^");