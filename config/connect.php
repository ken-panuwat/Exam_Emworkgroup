<?php
// Database connection setup
$servername = "localhost";
$username = "root";
$password = ""; 

try {
  $condb = new PDO("mysql:host=$servername;dbname=income_expense_system;charset=utf8", $username, $password);
  $condb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  //echo "Connected successfully";
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}

?>