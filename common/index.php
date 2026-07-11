<?php 
$servername = "103.134.89.115";
$username = "bd_navysailor_unlock";
$password = "sTe09*sTe09*";

try {
  $conn = new PDO("mysql:host=$servername;dbname=un_bd_navy", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Connected successfully";
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}



 