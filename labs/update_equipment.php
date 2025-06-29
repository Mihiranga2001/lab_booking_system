<?php
session_start();
include '../db_connect.php';

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Lab_TO') {
    header("Location: ../login/login_form.html");
    exit;
}

$equipment_id = isset($_GET['equipment_id']) ? intval($_GET['equipment_id']) : 0;
$new_status = isset($_GET['status']) ? intval($_GET['status']) : -1;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$user_id = $_SESSION['user_id'] ?? 1;

// Validation
if ($equipment_id <= 0) {
    die("Error: Invalid equipment ID");
}

if (!in_array($new_status, [0, 1])) {
    die("Error: Invalid status. Must be 0 or 1");
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    die("Error: Invalid date format");
}

// Check if equipment exists
$equipment_check = $conn->prepare("SELECT Equipment_Name FROM Lab_Equipment WHERE Equipment_ID = ?");
$equipment_check->bind_param("i", $equipment_id);
$equipment_check->execute();
$equipment_result = $equipment_check->get_result();

if ($equipment_result->num_rows == 0) {
    die("Error: Equipment not found");
}

$equipment_data = $equipment_result->fetch_assoc();
$equipment_name = $equipment_data['Equipment_Name'];

// Check if Equipment_Availability table exists
$table_check = $conn->query("SHOW TABLES LIKE 'Equipment_Availability'");
if ($table_check->num_rows == 0) {
    // Create the table if it doesn't exist
    $create_table = "CREATE TABLE Equipment_Availability (
        id INT AUTO_INCREMENT PRIMARY KEY,
        Equipment_ID INT NOT NULL,
        availability_date DATE NOT NULL,
        is_available BOOLEAN DEFAULT TRUE,
        notes TEXT,
        updated_by INT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_equipment_date (Equipment_ID, availability_date)
    )";
    $conn->query($create_table);
}

// Insert or update date-specific availability
$sql = "INSERT INTO Equipment_Availability (Equipment_ID, availability_date, is_available, updated_by) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        is_available = VALUES(is_available), 
        updated_by = VALUES(updated_by),
        updated_at = CURRENT_TIMESTAMP";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("isii", $equipment_id, $date, $new_status, $user_id);

if ($stmt->execute()) {
    // Redirect with success message
    $redirect_url = "view_equipments.php?date=" . urlencode($date) . "&updated=1&equipment=" . urlencode($equipment_name);
    header("Location: $redirect_url");
    exit;
} else {
    die("Execute failed: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>
