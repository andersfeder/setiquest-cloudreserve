<?php

require_once "api/database.inc.php";

$result = mysql_query("SELECT * FROM cr_requests WHERE request_id='" . $_GET['id'] . "'");
$request = mysql_fetch_assoc($result);

?>
<html>
  <head>
    <title>Confirm reservation request</title>
  </head>
  <body>
    <h3>Confirm reservation request</h3>
    <form method="POST" action="api/ConfirmRequest/">
      <input type="hidden" name="id" value="<?php echo $request['request_id'] ?>">
      Your e-mail address: <?php echo $request['owner'] ?><br>
      Request title: <?php echo $request['title'] ?><br>
      Request description:<br>
      <?php echo $request['description'] ?><br>
      VM image ID: <?php echo $request['imageid'] ?><br>
      Instance type: <?php echo $request['instancetype'] ?><br>
      Number of instances, minimum: <?php echo $request['mincount'] ?><br>
      Number of instances, maximum: <?php echo $request['maxcount'] ?><br>
      Reservation start time: <?php echo $request['begins'] ?></br>
      Reservation duration: <?php echo $request['duration'] ?> minutes</br>
      Enter your public key (for SSH access):<br>
      <textarea name="publickey"></textarea><br>
      <input type="checkbox" name="launch" value="true" checked> Launch requested instances immediately when reservation begins.<br>
      <input type="submit" name="action" value="Confirm">
      <input type="submit" name="action" value="Cancel">
    </form>
  </body>
</html>
