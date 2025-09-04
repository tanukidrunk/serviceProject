<?php
error_reporting(E_ALL);
error_reporting(-1);
ini_set('error_reporting', E_ALL);

$servername="151.106.124.154";
$username="u583789277_LabdbG6";
$password="Assign2567";
$dbname= "u583789277_LabdbG6";
$conn = new mysqli($servername, $username, $password,$dbname);

if ($conn->connect_error) {
    die("connection failed: " . $conn->connect_error);
  }
  

?>