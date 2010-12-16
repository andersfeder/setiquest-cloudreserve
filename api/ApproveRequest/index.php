<?php

require_once "../config.inc.php";
require_once "../util.inc.php";
require_once "../database.inc.php";
require_once "../mail.inc.php";

if ($return_url = @$_POST['return']) {
  if (filter_var($return_url, FILTER_VALIDATE_URL)) {
    function return_error($message) {
      global $return_url;
      $components = parse_url($return_url);
      $nonce = @$_POST['nonce'];
      $components['query'] .= "&nonce=$nonce&message=$message";
      $return_url = glue_url($components);
      header("Location: $return_url");
      die();
    }
  } else {
    print_error("Invalid return URL.");
  }
} else {
  function return_error($message) {
    print_error($message);
  }
}

if (!filter_var($owner = @$_POST['owner'], FILTER_VALIDATE_EMAIL))
  return_error("Invalid e-mail address.");

if (!($owner = filter_var($owner, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)))
  return_error("Empty e-mail address.");
if (!($title = filter_var(@$_POST['title'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)))
  return_error("Empty title.");
if (!($description = filter_var(@$_POST['description'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)))
  return_error("Empty description.");
if (!($imageid = filter_var(@$_POST['imageid'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)))
  return_error("Empty VM image ID.");

if (!in_array($instancetype = @$_POST['instancetype'], array(
  't1.micro',
  'm1.small',
  'm1.xlarge',
  'm2.xlarge',
  'm2.2xlarge',
  'm2.4xlarge',
  'c1.medium',
  'c1.xlarge',
  'cc1.4xlarge',
  'cg1.4xlarge')))
  return_error("Invalid instance type.");

if (($mincount = @$_POST['mincount']) < 1)
  return_error("Minimum number of instances must exceed zero.");

if ($mincount > 20)
  return_error("Minimum number of instances must not exceed 20.");

if (($maxcount = @$_POST['maxcount']) < $mincount)
  return_error("Maximum number of instances must exceed minimum number of instances.");

if ($maxcount > 20)
  return_error("Maximum number of instances must not exceed 20.");

if ($begins = @$_POST['begins'])
  if ($begins = strtotime($begins)) {
    if ($begins < time())
      return_error("Reservation must begin in the future.");
    else
      $begins = date("Y-m-d H:i:s", $begins);
  } else {
    return_error("Reservation start time must be a time or blank.");
  }

if (strlen($duration = @$_POST['duration']))
  if (is_int($duration+0)) {
    if (($ends = $begins + $duration * 60) <= $begins)
      return_error("Reservation must end after it has begun.");
  } else {
    return_error("Reservation duration must be a integer or blank.");
  }

if (strlen($includes = trim(@$_POST['includes'])))
  foreach (explode("\n", $includes) as $lineno => $line)
    if (!filter_var(trim($line), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED & FILTER_FLAG_HOST_REQUIRED))
      return_error("Boot script must be an URL (line " . $lineno + 1 . ").");

// Validation finished.

$id = nonce();
$beginss = empty($begins) ? 'NULL': "'$begins'";

// Insert request into database.

$query = sprintf("INSERT INTO cr_requests (request_id,owner,title,description,imageid,instancetype,mincount,maxcount,begins,duration,includes) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s',%s,'%s','%s')",
    $id,
    mysql_real_escape_string($owner),
    mysql_real_escape_string($title),
    mysql_real_escape_string($description),
    mysql_real_escape_string($imageid),
    mysql_real_escape_string($instancetype),
    $mincount,
    $maxcount,
    $beginss,
    $duration,
    mysql_real_escape_string($includes)
);

if (!mysql_query($query))
  return_error("Could not insert request in database.");

// Send confirmation e-mail to request owner.
$to = $owner;
$subject = "Confirm reservation request".($begins ? ": $begins" : "");
$body = "A request to reserve resources in the cloud has been registered from your e-mail address.

Title: $title
Description: $description
VM image ID: $imageid
Instance type: $instancetype
Min. instances: $mincount
Max. instances: $maxcount
Start time: $begins
Duration: $duration
Boot scripts:
$includes
  
To confirm that you have registered this request, please visit this page:
http://" . $_SERVER['HTTP_HOST']  . dirname(dirname(dirname($_SERVER['PHP_SELF']))) . "/confirm.php?id=$id

If you did not order this request, you can safely ignore this e-mail; we apologize for the inconvenience.
  
Best regards,
The Administrators";

$result = cr_mail($to, $subject, $body);

if ($result)
  return_error("Could not send confirmation e-mail.");
else
  die("A confirmation message for this request has been sent to your e-mail address.");

?>
