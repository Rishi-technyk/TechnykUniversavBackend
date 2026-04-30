<?php
$user='aept3433_aepta_new';
$pwd='?Z(*5;jR#}hG';
$host='localhost';
$db = 'aept3433_aepta_new';
$mysqli = new mysqli($host, $user, $pwd, $db);
//$connection = mysqli_connect($host, $user, $pwd, $db);
if ($mysqli->connect_errno){
  echo "Failed to connect to Database: "; die;
}
//mysqli_select_db($connection);
?>