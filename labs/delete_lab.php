<?php
include '../db_connect.php';

$lab_id = $_GET['lab_id'];

$sql = "DELETE FROM Lab WHERE Lab_ID = '$lab_id'";

if ($conn->query($sql) === TRUE) {
    echo "Lab deleted successfully.<br><a href='view_labs.php'>Back to Labs</a>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
