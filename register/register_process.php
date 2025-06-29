<?php
session_start();
require_once '../db_connect.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register_form.html");
    exit;
}

// Get and sanitize form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? '';

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = "Full name is required.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email address is required.";
}

if (empty($password)) {
    $errors[] = "Password is required.";
} elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters long.";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

if (empty($role) || !in_array($role, ['Student', 'Instructor', 'Lecture_in_charge', 'Lab_TO'])) {
    $errors[] = "Please select a valid role.";
}

// If there are validation errors, show them
if (!empty($errors)) {
    echo "<div style='color: red; padding: 20px; font-family: Arial, sans-serif;'>";
    echo "<h3>Registration Failed:</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    echo "<a href='register_form.html' style='color: blue;'>Go back to registration form</a>";
    echo "</div>";
    exit;
}

// Map role to table, ID column, and ranges
$table_mapping = [
    'Lecture_in_charge' => [
        'table' => 'lecture_in_charge', 
        'id_column' => 'LIC_ID',
        'min_id' => 1,
        'max_id' => 100
    ],
    'Student' => [
        'table' => 'student', 
        'id_column' => 'Student_ID',
        'min_id' => 101,
        'max_id' => 200
    ],
    'Instructor' => [
        'table' => 'instructor', 
        'id_column' => 'Instructor_ID',
        'min_id' => 201,
        'max_id' => 300
    ],
    'Lab_TO' => [
        'table' => 'lab_to', 
        'id_column' => 'TO_ID',
        'min_id' => 301,
        'max_id' => 400
    ]
];

$table_info = $table_mapping[$role];
$table_name = $table_info['table'];
$id_column = $table_info['id_column'];
$min_id = $table_info['min_id'];
$max_id = $table_info['max_id'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if the role has reached maximum capacity
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table_name");
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $current_count = $count_row['count'];
    $max_capacity = $max_id - $min_id + 1;

    if ($current_count >= $max_capacity) {
        throw new Exception("Registration for $role is currently full. Maximum capacity ($max_capacity) has been reached.");
    }

    // Check if email already exists in any of the user tables
    $email_check_tables = ['student', 'instructor', 'lecture_in_charge', 'lab_to'];
    $email_exists = false;

    foreach ($email_check_tables as $check_table) {
        $check_stmt = $conn->prepare("SELECT Email FROM $check_table WHERE Email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $email_exists = true;
            break;
        }
        $check_stmt->close();
    }

    if ($email_exists) {
        throw new Exception("An account with this email address already exists.");
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL statement based on role
    switch ($role) {
        case 'Student':
            $stmt = $conn->prepare("INSERT INTO student (Name, Email, Password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            break;

        case 'Instructor':
            $stmt = $conn->prepare("INSERT INTO instructor (Name, Email, Password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            break;

        case 'Lecture_in_charge':
            $stmt = $conn->prepare("INSERT INTO lecture_in_charge (Name, Email, Password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            break;

        case 'Lab_TO':
            $stmt = $conn->prepare("INSERT INTO lab_to (Name, Email, Password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            break;

        default:
            throw new Exception("Invalid role selected.");
    }

    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Failed to create account: " . $stmt->error);
    }

    // Get the inserted user ID
    $user_id = $conn->insert_id;

    // Verify the ID is in the correct range
    if ($user_id < $min_id || $user_id > $max_id) {
        throw new Exception("ID assignment error. Please contact administrator.");
    }

    // Commit transaction
    $conn->commit();

    // Close statement
    $stmt->close();

    // Success message with ID range information
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Registration Successful</title>
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
            .success-container {
                background: white;
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
                text-align: center;
                max-width: 500px;
                width: 100%;
            }
            .success-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(45deg, #10b981, #059669);
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
            p {
                color: #64748b;
                font-size: 16px;
                margin-bottom: 24px;
                line-height: 1.6;
            }
            .user-info {
                background: #f8fafc;
                padding: 20px;
                border-radius: 12px;
                margin-bottom: 24px;
                text-align: left;
            }
            .user-info strong {
                color: #1e293b;
                font-weight: 600;
            }
            .id-range {
                background: #e0f2fe;
                color: #0369a1;
                padding: 12px;
                border-radius: 8px;
                margin-top: 12px;
                font-size: 14px;
                border: 1px solid #bae6fd;
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
        <div class='success-container'>
            <div class='success-icon'>
                <i class='fas fa-check'></i>
            </div>
            <h1>Registration Successful!</h1>
            <p>Your account has been created successfully with ID in the assigned range.</p>
            
            <div class='user-info'>
                <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Role:</strong> " . str_replace('_', ' ', htmlspecialchars($role)) . "</p>
                <p><strong>User ID:</strong> " . $user_id . "</p>
                <div class='id-range'>
                    <strong>ID Range for " . str_replace('_', ' ', $role) . ":</strong> $min_id - $max_id
                </div>
            </div>
            
            <a href='../login/login_form.html' class='btn'>
                <i class='fas fa-sign-in-alt' style='margin-right: 8px;'></i>
                Sign In Now
            </a>
            <a href='../index.html' class='btn secondary'>
                <i class='fas fa-home' style='margin-right: 8px;'></i>
                Go to Home
            </a>
        </div>
    </body>
    </html>";

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Error message
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Registration Failed</title>
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
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 40px rgba(99, 102, 241, 0.4);
            }
        </style>
    </head>
    <body>
        <div class='error-container'>
            <div class='error-icon'>
                <i class='fas fa-exclamation-triangle'></i>
            </div>
            <h1>Registration Failed</h1>
            <div class='error-message'>
                " . htmlspecialchars($e->getMessage()) . "
            </div>
            <a href='register_form.html' class='btn'>
                <i class='fas fa-arrow-left' style='margin-right: 8px;'></i>
                Try Again
            </a>
        </div>
    </body>
    </html>";
}

// Close database connection
$conn->close();
?>
