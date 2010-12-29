<?php

require_once "config.inc.php";
require_once "util.inc.php";

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
      includes TEXT,
      launch BOOLEAN DEFAULT TRUE
    );");
mysql_query("CREATE TABLE IF NOT EXISTS cr_confirmations (
      confirmation_id CHAR(25) NOT NULL PRIMARY KEY,
      request_id CHAR(25) NOT NULL,
      time TIMESTAMP(8),
      publickey VARCHAR(2048),
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
mysql_query("CREATE TABLE IF NOT EXISTS cr_sessions (
      session_id CHAR(25) NOT NULL PRIMARY KEY,
      approval_id CHAR(25) NOT NULL,
      time TIMESTAMP(8),
      expires DATETIME,
      FOREIGN KEY (approval_id) REFERENCES cr_approvals(approval_id)
    );");
mysql_query("CREATE TABLE IF NOT EXISTS cr_instances (
      instance_id CHAR(25) NOT NULL PRIMARY KEY,
      session_id CHAR(25) NOT NULL,
      time TIMESTAMP(8),
      external_id CHAR(10),
      FOREIGN KEY (session_id) REFERENCES cr_sessions(session_id)
    );");
mysql_query("CREATE TABLE IF NOT EXISTS cr_bills (
      bill_id CHAR(25) NOT NULL PRIMARY KEY,
      instance_id CHAR(25) NOT NULL,
      time TIMESTAMP(8),
      FOREIGN KEY (instance_id) REFERENCES cr_instances(instance_id)
    );");

function cr_dbget_allocation($approval_id) {
  $query = sprintf("SELECT * FROM cr_requests,cr_confirmations,cr_approvals WHERE
    cr_approvals.approval_id='%s' AND
    cr_confirmations.confirmation_id=cr_approvals.confirmation_id AND
    cr_requests.request_id=cr_confirmations.request_id",
     mysql_real_escape_string($approval_id)
  );
  if ($result = mysql_query($query))
    return mysql_fetch_assoc($result);
}

function cr_dbget_session($session_id) {
  $query = sprintf("SELECT * FROM cr_requests,cr_confirmations,cr_approvals,cr_sessions WHERE
    cr_sessions.session_id='%s' AND
    cr_approvals.approval_id=cr_sessions.approval_id AND
    cr_confirmations.confirmation_id=cr_approvals.confirmation_id AND
    cr_requests.request_id=cr_confirmations.request_id",
    mysql_real_escape_string($session_id)
  );
  if ($result = mysql_query($query))
    return mysql_fetch_assoc($result);
}

function cr_dbget_session_byapproval($approval_id) {
  $query = sprintf("SELECT * FROM cr_requests,cr_confirmations,cr_approvals,cr_sessions WHERE
    cr_sessions.approval_id='%s' AND
    cr_approvals.approval_id=cr_sessions.approval_id AND
    cr_confirmations.confirmation_id=cr_approvals.confirmation_id AND
    cr_requests.request_id=cr_confirmations.request_id",
    mysql_real_escape_string($approval_id)
  );
  if ($result = mysql_query($query))
    return mysql_fetch_assoc($result);
}

function cr_dbadd_instances($session_id, $maxcount) {
  if ($session = cr_dbget_session($session_id)) {
    for ($i = 1; $i <= $maxcount; $i++) {
      if (mysql_query("LOCK TABLES cr_instances WRITE, cr_sessions READ")) {
        $query = sprintf("SELECT COUNT(cr_instances.instance_id) FROM cr_instances,cr_sessions WHERE cr_sessions.approval_id='%s' AND cr_instances.session_id=cr_sessions.session_id",
          $session['approval_id']
        );
        if ($row = mysql_fetch_assoc(mysql_query($query)) && $row['COUNT(cr_instances.instance_id)'] < $session['maxcount']) {
          $query = sprintf("INSERT INTO cr_instances (instance_id, session_id) VALUES ('%s','%s')",
            $instance_id = nonce(),
            $session['session_id']
          );
          if ($result = mysql_query($query))
            $instance_ids[] = $instance_id;
        }
        mysql_query("UNLOCK TABLES");
      } else {
        die("Could not acquire lock on database.");
      }
    }
    return $instance_ids;
  } else {
    die("Could not retrieve session from database.");
  }
}

function cr_dbdel_instance($instance_id) {
  $query = sprintf("DELETE FROM cr_instances WHERE instance_id='%s'",
    $instance_id
  );
  if ($result = mysql_query($query))
    return true;
}

function cr_dbset_instance($instance_id, $session_id, $external_id) {
  $query = sprintf("REPLACE INTO cr_instances (instance_id, session_id, external_id) VALUES ('%s','%s','%s')",
    $instance_id,
    $session_id,
    $external_id
  );
  if ($result = mysql_query($query))
    return true;
  else
    die("Could not save instance to database.");
}

function cr_dbget_instance($instance_id) {
  $query = sprintf("SELECT * FROM cr_requests,cr_confirmations,cr_approvals,cr_sessions,cr_instances WHERE
    cr_instances.instance_id='%s' AND
    cr_sessions.session_id=cr_instances.session_id AND
    cr_approvals.approval_id=cr_sessions.approval_id AND
    cr_confirmations.confirmation_id=cr_approvals.confirmation_id AND
    cr_requests.request_id=cr_confirmations.request_id",
    mysql_real_escape_string($instance_id)
  );
  if ($result = mysql_query($query))
    return mysql_fetch_assoc($result);
}

function cr_dbget_instances($session_id) {
  $query = sprintf("SELECT * FROM cr_requests,cr_confirmations,cr_approvals,cr_sessions,cr_instances WHERE
    cr_instances.session_id='%s' AND
    cr_sessions.session_id=cr_instances.session_id AND
    cr_approvals.approval_id=cr_sessions.approval_id AND
    cr_confirmations.confirmation_id=cr_approvals.confirmation_id AND
    cr_requests.request_id=cr_confirmations.request_id",
    mysql_real_escape_string($session_id)
  );
  if ($result = mysql_query($query)) {
    while ($row = mysql_fetch_assoc($result)) {
      $rows[] = $row;
    }
    return $rows;
  }
}

function cr_dbget_instance_byexternal($external_id) {
  $query = sprintf("SELECT * FROM cr_requests,cr_confirmations,cr_approvals,cr_sessions,cr_instances WHERE
    cr_instances.external_id='%s' AND
    cr_session.session_id=cr_instances.session_id AND
    cr_approvals.approval_id=cr_sessions.approval_id AND
    cr_confirmations.confirmation_id=cr_approvals.confirmation_id AND
    cr_requests.request_id=cr_confirmations.request_id",
    mysql_real_escape_string($external_id)
  );
  if ($result = mysql_query($query))
    return mysql_fetch_assoc($result);
}

?>
