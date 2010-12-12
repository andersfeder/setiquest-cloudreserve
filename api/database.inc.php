<?php

require_once "config.inc.php";

// Connecting, selecting database
$cr_db_link = mysql_connect($cr_db_host, $cr_db_username, $cr_db_password)
    or die('Could not connect: ' . mysql_error());
mysql_select_db($cr_db_database)
    or die('Could not select database');

// Create tables if they don't already exist
mysql_query("CREATE TABLE IF NOT EXISTS cr_requests (
      id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      nonce CHAR(25),
      owner VARCHAR(256),
      time TIMESTAMP(8),
      title VARCHAR(256),
      description VARCHAR(512),
      imageid VARCHAR(32),
      mincount SMALLINT,
      maxcount SMALLINT,
      instancetype VARCHAR(16),
      begins DATETIME,
      duration MEDIUMINT
    );");
mysql_query("CREATE TABLE IF NOT EXISTS cr_confirmations (
      request_id INT NOT NULL PRIMARY KEY,
      time TIMESTAMP(8),
      publickey VARCHAR(512),
      FOREIGN KEY (request_id) REFERENCES cr_requests(id) ON DELETE CASCADE
    );");
mysql_query("CREATE TABLE IF NOT EXISTS cr_approvals (
      request_id INT NOT NULL PRIMARY KEY,
      admin VARCHAR(256),
      time TIMESTAMP(8),
      FOREIGN KEY (request_id) REFERENCES cr_requests(id) ON DELETE CASCADE
    );");
?>
