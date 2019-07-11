<?php
function dbconnect() {
  $mysqli = new mysqli("localhost", "root", null, "epiz_20874118_jsf", 3306);
  if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}
  return $mysqli;
}

?>