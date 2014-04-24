CREATE TABLE csi_log
(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	test_name VARCHAR(256),
	host_name VARCHAR(256),
	create_time INTEGER,
	test_result INTEGER NOT NULL,
	rc INTEGER,
	exec_duration INTEGER,
	kaltura_version VARCHAR(10)
);

CREATE TABLE success_rates
(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
   	failed INTEGER,
	successful INTEGER, 
	kaltura_version VARCHAR(10)
);
