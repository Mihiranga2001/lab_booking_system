<?php
session_start();
include '../db_connect.php';

// Security: Check if user is Lab TO
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Lab_TO') {
    header("Location: ../login/login_form.html");
    exit;
}

// Initialize variables
$lab_id = '';
$new_status = '';
$date = '';
$success = false;
$error_message = '';
$lab_name = '';

// Validate and sanitize input
if (isset($_GET['lab_id']) && isset($_GET['status']) && isset($_GET['date'])) {
    $lab_id = intval($_GET['lab_id']);
    $new_status = intval($_GET['status']); // 1 or 0
    $date = $_GET['date'];
    $user_id = $_SESSION['user_id'];
    
    // Validation
    if ($lab_id <= 0) {
        $error_message = "Invalid lab ID provided.";
    } elseif (!in_array($new_status, [0, 1])) {
        $error_message = "Invalid status value. Must be 0 or 1.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $error_message = "Invalid date format.";
    } else {
        // Get lab name for confirmation
        $lab_query = $conn->prepare("SELECT Lab_Name FROM Lab WHERE Lab_ID = ?");
        $lab_query->bind_param("i", $lab_id);
        $lab_query->execute();
        $lab_result = $lab_query->get_result();
        
        if ($lab_result->num_rows > 0) {
            $lab_data = $lab_result->fetch_assoc();
            $lab_name = $lab_data['Lab_Name'];
            
            // Insert or update date-specific availability
            // This will only affect the selected date, not other dates
            $sql = "INSERT INTO Lab_Availability (Lab_ID, availability_date, is_available, updated_by) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    is_available = VALUES(is_available), 
                    updated_by = VALUES(updated_by),
                    updated_at = CURRENT_TIMESTAMP";
            
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("isii", $lab_id, $date, $new_status, $user_id);
                
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $error_message = "Database error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error_message = "Failed to prepare statement: " . $conn->error;
            }
        } else {
            $error_message = "Lab not found with ID: " . $lab_id;
        }
    }
} else {
    $error_message = "Missing required parameters (lab_id, status, and date).";
}

$conn->close();

// Redirect immediately if successful
if ($success) {
    $redirect_url = "view_labs.php?date=" . urlencode($date) . "&updated=1&lab=" . urlencode($lab_name);
    header("Location: " . $redirect_url);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date-Specific Lab Update Result</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .container { max-width: 500px; width: 100%; background: white; border-radius: 24px; box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2); overflow: hidden; animation: slideUp 0.8s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        .error-header { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 50px 40px; text-align: center; }
        .error-icon { width: 80px; height: 80px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; margin: 0 auto 20px; }
        .header-title { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
        .content { padding: 40px; text-align: center; }
        .error-details { background: #fef2f2; color: #dc2626; padding: 20px; border-radius: 12px; margin-bottom: 24px; border-left: 4px solid #ef4444; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; border-radius: 12px; font-weight: 600; transition: all 0.3s ease; margin: 0 8px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4); }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-header">
            <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h1 class="header-title">Date-Specific Update Failed</h1>
            <p>Unable to update lab availability for the selected date</p>
        </div>
        <div class="content">
            <div class="error-details">
                <strong>Error:</strong> <?= htmlspecialchars($error_message) ?><br>
                <strong>Date:</strong> <?= htmlspecialchars($date) ?><br>
                <strong>Lab ID:</strong> <?= htmlspecialchars($lab_id) ?>
            </div>
            <a href="view_labs.php?date=<?= urlencode($date) ?>" class="btn">
                <i class="fas fa-arrow-left"></i> Back to Labs
            </a>
        </div>
    </div>
</body>
</html>
