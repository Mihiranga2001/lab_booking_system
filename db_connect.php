<?php
$host = "localhost";
$user = "root";
$password = ""; // Add your password if needed
$dbname = "LAB_Booking_System";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
