<?php

require_once "api/database.inc.php";
require_once "api/cloud.inc.php";

$query = "SELECT * FROM cr_requests,cr_confirmations,cr_approvals WHERE cr_approvals.approval_id='" . $_GET['id'] . "' AND cr_confirmations.confirmation_id=cr_approvals.confirmation_id AND cr_requests.request_id=cr_confirmations.request_id";
if(!($result = mysql_query($query)))
  die("Could not retrieve reservation from database.");
$allocation = mysql_fetch_assoc($result);

?>
<html>
  <head>
    <title>Manage reservation</title>
  </head>
  <body>
    <h3>Manage reservation</h3>
    Owner: <?php echo $allocation['owner'] ?><br>
    Request title: <?php echo $allocation['title'] ?><br>
    Request description:<br>
    <?php echo $allocation['description'] ?><br>
    VM image ID: <?php echo $allocation['imageid'] ?><br>
    Instance type: <?php echo $allocation['instancetype'] ?><br>
    Number of instances, minimum: <?php echo $allocation['mincount'] ?><br>
    Number of instances, maximum: <?php echo $allocation['maxcount'] ?><br>
    Reservation start time: <?php echo $allocation['begins'] ?><br>
    Reservation duration: <?php echo $allocation['duration'] ?> minutes<br>
    Boot scripts:<br>
    <?php echo $allocation['includes'] ?><br>
<?php

$session = cr_dbget_session_byapproval($allocation['approval_id']);
if ($session) {
?>
    <h4>Running instances</h4>
    <form method="POST" action="api/ManageInstance/">
      <table>
        <tr>
          <th>Select</th>
          <th>Instance ID</th>
          <th>Public DNS</th>
          <th>State</th>
          <th>Launch Time</th>
        </tr>
<?php
  $opt = array(
    'Filter' => array(
      array(
        'Name' => 'key-name',
        'Value' => 'cloudreserve-'.$session['approval_id']
      )
    )
  );
  $externals = cr_cloud_describe_instances(null, $opt);
  foreach ($externals as $external) {
    $real_instances[(string)$external->instanceId] = $external;
  }

  $instances = cr_dbget_instances($session['session_id']);
  foreach ($instances as $instance) {
    // Match real instance.
    $real_instance = $real_instances[$instance['external_id']];
?>
      <tr>
        <td>
          <input type="radio" name="instance" value="<?php echo $instance['instance_id'] ?>">
        </td>
        <td>
          <?php echo ($real_instance->instanceId) ?>
        </td>
        <td>
          <?php echo ($real_instance->dnsName) ?>
        </td>
        <td>
          <?php echo ($real_instance->instanceState->name) ?>
        </td>
        <td>
          <?php echo ($real_instance->launchTime) ?>
        </td>
      </tr>
<?php
  }
?>
      </table>
      <input type="submit" name="action" value="Resume">
      <input type="submit" name="action" value="Stop">
      <input type="submit" name="action" value="Terminate">
    </form>
    <h4>Launch instances</h4>
    <form method="POST" action="api/LaunchInstances/">
      <input type="hidden" name="id" value="<?php echo $session['session_id'] ?>">
      Number of instances, minimum: <input name="mincount" type="text" value="1"><br>
      Number of instances, maximum: <input name="maxcount" type="text" value="1"><br>
     <input type="submit" value="Launch">
    </form>
<?php
  } else {
?>
    <h4>Launch instances</h4>
    <form method="POST" action="api/ConfigureRequest/">
      <input type="hidden" name="id" value="<?php echo $allocation['request_id'] ?>">
      <input type="checkbox" name="launch" value="true" <?php if($allocation['launch']) { ?>checked<?php } ?>> Launch requested instances immediately when reservation begins.<br>
     <input type="submit" value="Save">
    </form>
<?php
 }
?>
  </body>
</html>
