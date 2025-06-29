<?php
session_start();
require_once '../db_connect.php';

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login_form.html");
    exit;
}

// Get and sanitize form data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

// Input validation
if (empty($email) || empty($password) || empty($role)) {
    die("All fields are required. <a href='login_form.html'>Go back</a>");
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email format. <a href='login_form.html'>Go back</a>");
}

// Map role to correct table name and ID column
$role_mapping = [
    'Student' => [
        'table' => 'student',
        'id_column' => 'Student_ID'
    ],
    'Instructor' => [
        'table' => 'instructor', 
        'id_column' => 'Instructor_ID'
    ],
    'Lecture_in_charge' => [
        'table' => 'lecture_in_charge',
        'id_column' => 'LIC_ID'
    ],
    'Lab_TO' => [
        'table' => 'lab_to',
        'id_column' => 'TO_ID'
    ]
];

// Check if role is valid
if (!isset($role_mapping[$role])) {
    die("Invalid role selected. <a href='login_form.html'>Go back</a>");
}

$table_info = $role_mapping[$role];
$table = $table_info['table'];
$column_id = $table_info['id_column'];

try {
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM $table WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Check password (supports both hashed and plain text passwords)
        $password_valid = false;
        
        // Check if password is hashed (new registrations)
        if (password_get_info($user['Password'])['algo'] !== null) {
            // Password is hashed, use password_verify()
            $password_valid = password_verify($password, $user['Password']);
        } else {
            // Password is plain text (old registrations), compare directly
            $password_valid = ($password === $user['Password']);
        }
        
        if ($password_valid) {
            // Login successful - set session variables
            $_SESSION['user_id'] = $user[$column_id];
            $_SESSION['role'] = $role;
            $_SESSION['name'] = $user['Name'];
            $_SESSION['email'] = $user['Email'];
            
            // Redirect to index.php instead of dashboard.php
            header("Location: ../index.php");
            exit;
        } else {
            // Invalid password
            showErrorPage("Invalid password. Please try again.");
        }
    } else {
        // User not found
        showErrorPage("No account found with this email address for the selected role.");
    }
    
    $stmt->close();

} catch (Exception $e) {
    // Database error
    showErrorPage("Database error occurred. Please try again later.");
}

$conn->close();

// Function to display professional error page
function showErrorPage($message) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Login Failed - Lab Booking System</title>
        <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap' rel='stylesheet'>
        <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
        <style>
            body {
                font-family: 'Inter', sans-serif;
                background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
                padding: 20px;
            }
            .error-container {
                background: white;
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
                text-align: center;
                max-width: 500px;
                width: 100%;
            }
            .error-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(45deg, #ef4444, #dc2626);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 24px;
                color: white;
                font-size: 36px;
            }
            h1 {
                color: #1e293b;
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 16px;
            }
            .error-message {
                background: #fef2f2;
                color: #dc2626;
                padding: 16px;
                border-radius: 12px;
                margin-bottom: 24px;
                border: 1px solid #fecaca;
                font-weight: 500;
            }
            .btn {
                display: inline-block;
                padding: 14px 28px;
                background: linear-gradient(135deg, #6366f1, #8b5cf6);
                color: white;
                text-decoration: none;
                border-radius: 12px;
                font-weight: 600;
                font-size: 16px;
                transition: all 0.3s ease;
                margin: 0 8px;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 40px rgba(99, 102, 241, 0.4);
            }
            .btn.secondary {
                background: linear-gradient(135deg, #64748b, #475569);
            }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <div class='error-icon'>
                <i class='fas fa-exclamation-triangle'></i>
            </div>
            <h1>Login Failed</h1>
            <div class='error-message'>
                " . htmlspecialchars($message) . "
            </div>
            <a href='login_form.html' class='btn'>
                <i class='fas fa-arrow-left' style='margin-right: 8px;'></i>
                Try Again
            </a>
            <a href='../index.html' class='btn secondary'>
                <i class='fas fa-home' style='margin-right: 8px;'></i>
                Go to Home
            </a>
        </div>
    </body>
    </html>";
    exit;
}
?>
