<?php
$db = array();

$db[name] = "DATABASE NAME"
$db[host] = "DATABASE HOST";
$db[user] = "DATABASE USER";
$db[pass] = "DATABASE PASSWORD";

$sql = new mysqli($db[host], $db[user], $db[pass], $db[name]);
if (mysqli_connect_errno()) {
  exit('Connect failed: '. mysqli_connect_error());
}
?>