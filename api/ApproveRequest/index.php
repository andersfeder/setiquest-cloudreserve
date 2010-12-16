<?php

require_once "../config.inc.php";
require_once "../database.inc.php";
require_once "../util.inc.php";
require_once "../mail.inc.php";

$query = "SELECT * FROM cr_requests,cr_confirmations WHERE cr_confirmations.confirmation_id='" . $_POST['id'] . "' AND cr_requests.request_id=cr_confirmations.request_id";
if (!($result = mysql_query($query)))
  die("Could not retrieve request from database.");
$request = mysql_fetch_assoc($result);

switch ($_POST['action']) {
  case 'Approve':
    // TODO: Validate input.
    $message = $_POST['message'];

    $query = sprintf("INSERT INTO cr_approvals (approval_id,confirmation_id,admin,message) VALUES ('%s','%s','%s','%s')",
        $id = nonce(),
        mysql_real_escape_string($_POST['id']),
        mysql_real_escape_string(CR_ROOT_ADMIN),
        $message
      );
    if (!mysql_query($query)) {
      die("Could not insert approval in database.");
    } else {
      $result = cr_mail(
        $request['owner'],
        "Reservation approved" . ($request['title'] ? ": " . $request['title'] : ""),
        "Your request to reserve resources in the cloud has been approved.

Message from administrator: ".$message."

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

To manage this reservation, please visit this page:
http://" . $_SERVER['HTTP_HOST']  . dirname(dirname(dirname($_SERVER['PHP_SELF']))) . "/manage.php?id=$id

Best regards,
The Administrators"
      );
      if ($result)
        die("Could not send approval e-mail to request author.");
        //TODO: What to do about approval here?
      else
        die("The reservation has been approved and the author has been notified.");
    }
    break;
  case 'Deny':
    if (!mysql_query("DELETE FROM cr_requests WHERE cr_requests.request_id = cr_confirmations.request_id AND cr_confirmations.confirmation_id='" . mysql_real_escape_string($_POST['id']) . "'"))
      die("Could not delete request from database.");
    else
      die("The request was denied.");
    break;
}

?>
