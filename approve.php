<?php

require_once "api/database.inc.php";

$query = "SELECT * FROM cr_requests, cr_confirmations WHERE cr_confirmations.confirmation_id='" . $_GET['id'] . "' AND cr_requests.request_id=cr_confirmations.request_id";
if ($result = mysql_query($query))
  $request = mysql_fetch_assoc($result);
else
  die("Could not retrieve request from database.");

?>
<html>
  <head>
    <title>Approve reservation request</title>
  </head>
  <body>
    <h3>Approve reservation request</h3>
    <form method="POST" action="api/ApproveRequest/">
      <input type="hidden" name="id" value="<?php echo $request['confirmation_id'] ?>">
      Author: <?php echo $request['owner'] ?><br>
      Request title: <?php echo $request['title'] ?><br>
      Request description:<br>
      <?php echo $request['description'] ?><br>
      VM image ID: <?php echo $request['imageid'] ?><br>
      Instance type: <?php echo $request['instancetype'] ?><br>
      Number of instances, minimum: <?php echo $request['mincount'] ?><br>
      Number of instances, maximum: <?php echo $request['maxcount'] ?><br>
      Reservation start time: <?php echo $request['begins'] ?></br>
      Reservation duration: <?php echo $request['duration'] ?> minutes.</br>
      Public key (for SSH access):<br>
      <?php echo $request['publickey'] ?><br>
      Launch requested instances immediately when reservation begins: <?php echo $request['launch'] ?><br>
      Message to author:<br>
      <textarea name="message"></textarea><br>
      <input type="submit" name="action" value="Approve">
      <input type="submit" name="action" value="Deny">
    </form>
  </body>
</html>
