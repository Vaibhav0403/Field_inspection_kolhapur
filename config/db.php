<?php
$servername = "localhost";
$username = "root"; // Your DB username
$password = "";     // Your DB password
$dbname = "inspection"; // Your DB name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>