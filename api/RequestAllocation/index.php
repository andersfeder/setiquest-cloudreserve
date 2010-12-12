<?php

require_once "../util.inc.php";
require_once "../database.inc.php";

function print_error($message) {
  die("<b>Error:</b> $message");
}

if ($return_url = @$_POST['return']) {
  if (filter_var($return_url, FILTER_VALIDATE_URL)) {
    function return_error($message) {
      $components = parse_url($return_url);
      $components['query'] .= "&message=$message";
      $return_url = glue_url($components);
      die($return_url);
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

if (($begins = strtotime(@$_POST['begins'])) < time())
  if (!(@$_POST['begins']))
    $begins = "";
  else
    return_error("Reservation must begin in the future.");

if (($ends = $begins + @$_POST['duration'] * 60) < $begins)
  return_error("Reservation must end after it has begun.");

echo "Validated.";

/*
$query = sprintf("INSERT INTO cr_requests (owner,) ",
    mysql_real_escape_string($firstname),
    mysql_real_escape_string($lastname));

// Perform Query
$result = mysql_query($query);
*/
?>
