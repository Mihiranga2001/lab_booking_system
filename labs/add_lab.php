<?php
session_start();
if ($_SESSION['role'] == 'Lab_TO') {
    echo "Access denied. You are not allowed to add labs.";
    exit;
}
include '../db_connect.php';

$lab_id = $_POST['lab_id'];
$lab_name = $_POST['lab_name'];
$capacity = $_POST['capacity'];
$availability = $_POST['availability'];

$sql = "INSERT INTO Lab (Lab_ID, Lab_Name, Capacity, Availability)
        VALUES ('$lab_id', '$lab_name', '$capacity', '$availability')";

if ($conn->query($sql) === TRUE) {
    echo "New lab added successfully!<br><a href='view_labs.php'>View Labs</a>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
