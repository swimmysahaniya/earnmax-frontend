<?php
$host = "localhost";
$username = "por_earn_nah";
$password = "Sfg#$34%^df%$";
$dbname = "m_max_e_earn";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>