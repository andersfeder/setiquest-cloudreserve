<?php

// URL for API return/callback.
define("CR_UI_REQUEST_RETURNURL", "http://" . $_SERVER['HTTP_HOST']  . dirname($_SERVER['PHP_SELF']) . "/return.php");

?>
<html>
  <head>
    <title>Request reservation</title>
  </head>
  <body>
    <h3>Request reservation</h3>
    <form method="POST" action="api/RequestAllocation/">
      <input type="hidden" name="return" value="<?php echo CR_UI_REQUEST_RETURNURL ?>">
      Your e-mail address: <input type="text" name="owner"/><br>
      Request title: <input type="text" name="title"><br>
      Request description:<br>
      <textarea name="description"></textarea><br>
      VM image ID: <input type="text" name="imageid" value="ami-3ac33653"/><br>
      Instance type: <select name="instancetype">
        <option value="t1.micro" selected="yes">Micro Instance</option>
        <option value="m1.small">Small Instance</option>
        <option value="m1.large">Large Instance</option>
        <option value="m1.xlarge">Extra Large Instance</option>
        <option value="m2.xlarge">High-Memory Extra Large Instance</option>
        <option value="m2.2xlarge">High-Memory Double Extra Large Instance</option>
        <option value="m2.4xlarge">High-Memory Quadruple Extra Large Instance</option>
        <option value="c1.medium">High-CPU Medium Instance</option>
        <option value="c1.xlarge">High-CPU Extra Large Instance</option>
        <option value="cc1.4xlarge">Cluster Compute Quadruple Extra Large Instance</option>
        <option value="cg1.4xlarge">Cluster GPU Quadruple Extra Large Instance</option>
      </select><br>
      Number of instances, minimum: <input type="text" name="mincount" value="1"/><br>
      Number of instances, maximum: <input type="text" name="maxcount" value="1"/><br>
      Reservation start time (yyyy-mm-dd hh:mm, or blank for ASAP): <input type="text" name="begins"></br>
      Reservation duration (minutes, or blank for indefinitely): <input type="text" name="duration"></br>
      Your public key (for SSH access):<br>
      <textarea name="publickey"></textarea><br>
      <input type="submit">
    </form>
  </body>
</html>
