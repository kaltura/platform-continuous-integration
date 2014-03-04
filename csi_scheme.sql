CREATE TABLE csi_log
(
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    test_name VARCHAR(256),
    create_time INTEGER,
    version VARCHAR(20) NOT NULL,
    test_result INTEGER NOT NULL,
    rc INTEGER,
    exec_duration INTEGER
);

