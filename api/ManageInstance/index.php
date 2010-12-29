<?php

require_once '../cloud.inc.php';
require_once '../database.inc.php';

//TODO: Validate input.

$instance = cr_dbget_instance($_POST['instance']);

switch ($_POST['action']) {
  case 'Resume':
    break;
  case 'Stop':
    break;
  case 'Terminate':
    cr_cloud_terminate_instance($instance['instance_id']);
    break;
}

?>
