CREATE TABLE IF NOT EXISTS server_stats(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    hostname VARCHAR(100),
    stat_name VARCHAR(20),
    stat_numerical decimal(18, 2),
    stat_data TEXT,
    dt_datetime datetime
);