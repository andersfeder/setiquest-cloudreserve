<?php

require_once "../util.inc.php";
require_once "../database.inc.php";

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

echo "Validated.";

/*
$query = sprintf("INSERT INTO cr_requests (owner,) ",
    mysql_real_escape_string($firstname),
    mysql_real_escape_string($lastname));

// Perform Query
$result = mysql_query($query);
*/

?>
