<?php

require_once "config.inc.php";

// Connecting, selecting database
$cr_db_link = mysql_connect(CR_DB_HOST, CR_DB_USERNAME, CR_DB_PASSWORD)
    or die('Could not connect: ' . mysql_error());
mysql_select_db(CR_DB_DATABASE)
    or die('Could not select database');

// Create tables if they don't already exist
mysql_query("CREATE TABLE IF NOT EXISTS cr_requests (
      request_id CHAR(25) NOT NULL UNIQUE PRIMARY KEY,
      nonce CHAR(25) NOT NULL UNIQUE,
      owner VARCHAR(256) NOT NULL,
      time TIMESTAMP(8),
      title VARCHAR(256),
      description VARCHAR(512),
      imageid VARCHAR(32) NOT NULL,
      instancetype VARCHAR(16) NOT NULL,
      mincount SMALLINT NOT NULL,
      maxcount SMALLINT NOT NULL,
      begins DATETIME,
      duration MEDIUMINT,
      includes TEXT
    );");
mysql_query("CREATE TABLE IF NOT EXISTS cr_confirmations (
      confirmation_id CHAR(25) NOT NULL PRIMARY KEY,
      request_id CHAR(25) NOT NULL,
      time TIMESTAMP(8),
      publickey VARCHAR(512),
      launch BOOLEAN,
      FOREIGN KEY (request_id) REFERENCES cr_requests(request_id) ON DELETE CASCADE
    );");
mysql_query("CREATE TABLE IF NOT EXISTS cr_approvals (
      approval_id CHAR(25) NOT NULL PRIMARY KEY,
      confirmation_id CHAR(25) NOT NULL,
      admin VARCHAR(256),
      time TIMESTAMP(8),
      message VARCHAR(512),
      FOREIGN KEY (confirmation_id) REFERENCES cr_confirmations(confirmation_id) ON DELETE CASCADE
    );");
?>
