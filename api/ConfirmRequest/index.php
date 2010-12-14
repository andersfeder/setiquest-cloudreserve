<?php

require_once "../database.inc.php";

switch ($_POST['action']) {
  case 'Confirm':
    // TODO: Validate input.
    $id = $_POST['id'];
    $publickey = $_POST['publickey'];
    $launch = (boolean)$_POST['launch'];
    $query = sprintf("INSERT INTO cr_confirmations (request_id,publickey,launch) VALUES ('%s','%s','%s')",
        mysql_real_escape_string($id),
        mysql_real_escape_string($publickey),
        $launch
      );
    if (!mysql_query($query))
      die("Could not insert confirmation in database. ".mysql_error());
    else
      die("Your request has been confirmed and is awaiting approval.");
    break;
  case 'Cancel':
    if (!mysql_query("DELETE FROM cr_requests WHERE nonce='" . mysql_real_escape_string($_POST['nonce']) . "'"))
      die("Could not delete request from database.");
    else
      die("The request was cancelled.");
    break;
}

?>
