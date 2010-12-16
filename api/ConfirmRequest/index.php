<?php

require_once "../config.inc.php";
require_once "../database.inc.php";
require_once "../util.inc.php";
require_once "../mail.inc.php";

switch ($_POST['action']) {
  case 'Confirm':
    if (!($result = mysql_query("SELECT * FROM cr_requests WHERE request_id='" . $_POST['id'] . "'")))
      die("Could not retrieve request from database.");
    $request = mysql_fetch_assoc($result);

    // TODO: Validate input.
    $publickey = $_POST['publickey'];
    $launch = (boolean)$_POST['launch'];

    $query = sprintf("INSERT INTO cr_confirmations (confirmation_id,request_id,publickey,launch) VALUES ('%s','%s','%s','%s')",
        $id = nonce(),
        mysql_real_escape_string($_POST['id']),
        mysql_real_escape_string($publickey),
        $launch
      );
    if (!mysql_query($query)) {
      die("Could not insert confirmation in database.");
    } else {
      // TODO: Send mail to groups of administrators.
      $result = cr_mail(
        CR_ROOT_ADMIN,
        "New reservation request" . ($request['title'] ? ": " . $request['title'] : ""),
        "A new request to reserve resources in the cloud has been registered.

Author: ".$request['owner']."
Title: ".$request['title']."
Description: ".$request['description']."
VM image ID: ".$request['imageid']."
Instance type: ".$request['instancetype']."
Min. instances: ".$request['mincount']."
Max. instances: ".$request['maxcount']."
Start time: ".$request['begins']."
Duration: ".$request['duration']." minutes
Boot scripts:
".$request['includes']."
        
To approve or deny this request, please visit this page:
http://" . $_SERVER['HTTP_HOST']  . dirname(dirname(dirname($_SERVER['PHP_SELF']))) . "/approve.php?id=$id

Best regards,
The Administrators"
      );
      if ($result)
        die("Could not send request e-mail to cloud administrator.");
        //TODO: What to do about request here?
      else
        die("Your request has been confirmed and is awaiting approval.");
    }
    break;
  case 'Cancel':
    if (!mysql_query("DELETE FROM cr_requests WHERE cr_request='" . mysql_real_escape_string($_POST['id']) . "'"))
      die("Could not delete request from database.");
    else
      die("The request was cancelled.");
    break;
}

?>
