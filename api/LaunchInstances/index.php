<?php
require_once "../database.inc.php";
require_once "../cloud.inc.php";
require_once "../util.inc.php";

if (!($session = cr_dbget_session($_POST['id'])))
  die("Session not found in database.");

// TODO: Validate input.
$_POST['mincount']=1;
$_POST['maxcount']=1;

cr_cloud_launch_instances($session['session_id'],$session['imageid'],$_POST['mincount'],$_POST['maxcount'],$session['approval_id']);

?>
