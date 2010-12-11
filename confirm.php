<?php

require_once "api/database.inc.php";

$result = mysql_query("SELECT * FROM cr_requests WHERE nonce='" . $_GET['nonce'] . "'");
$request = mysql_fetch_assoc($result);

?>
<html>
  <head>
    <title>Confirm reservation request</title>
  </head>
  <body>
    <h3>Confirm reservation request</h3>
    <form method="POST" action="api/ConfirmRequest/">
      Your e-mail address: <?php echo $request['owner'] ?><br>
      Request title: <?php echo $request['title'] ?><br>
      Request description:<br>
      <?php echo $request['description'] ?><br>
      VM image ID: <?php echo $request['imageid'] ?><br>
      Instance type: <?php echo $request['instancetype'] ?><br>
      Number of instances, minimum: <?php echo $request['mincount'] ?><br>
      Number of instances, maximum: <?php echo $request['maxcount'] ?><br>
      Reservation start time: <?php echo $request['begins'] ?></br>
      Reservation end time: <?php echo $request['duration'] ?></br>
      Enter your public key (for SSH access):<br>
      <textarea name="publickey"></textarea><br>
      <input type="submit" name="action" value="Confirm">
      <input type="submit" name="action" value="Cancel">
    </form>
  </body>
</html>
