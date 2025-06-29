<?php
session_start();
require_once '../db_connect.php';

// Check authentication and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Instructor') {
    header("Location: ../login/login_form.html");
    exit;
}

// Get form data
$instructor_id = $_SESSION['user_id'];
$instructor_name = $_SESSION['name'] ?? 'Unknown';
$lab_id = $_POST['lab_id'] ?? '';
$date = $_POST['date'] ?? '';
$time_slot = $_POST['time_slot'] ?? '';

// Validation
$errors = [];
if (empty($lab_id)) $errors[] = "Lab selection is required.";
if (empty($date)) $errors[] = "Date is required.";
if (empty($time_slot)) $errors[] = "Time slot is required.";

$success = false;
$booking_id = 0;
$lab_name = '';

try {
    if (empty($errors)) {
        // Get lab information
        $lab_stmt = $conn->prepare("SELECT Lab_Name FROM Lab WHERE Lab_ID = ?");
        $lab_stmt->bind_param("i", $lab_id);
        $lab_stmt->execute();
        $lab_result = $lab_stmt->get_result();
        
        if ($lab_result->num_rows > 0) {
            $lab_data = $lab_result->fetch_assoc();
            $lab_name = $lab_data['Lab_Name'];
            
            // *** CORRECTED: Assign 'Pending' to a variable first ***
            $status = 'Pending';
            $booking_stmt = $conn->prepare("INSERT INTO Lab_Booking (Lab_ID, Lab_Name, Request_Date, Request_Time_Slot, Status) VALUES (?, ?, ?, ?, ?)");
            $booking_stmt->bind_param("issss", $lab_id, $lab_name, $date, $time_slot, $status);
            
            if ($booking_stmt->execute()) {
                $booking_id = $conn->insert_id;
                
                // Link instructor to booking
                $instructor_stmt = $conn->prepare("INSERT INTO Instructor_Book_Booking (Instructor_ID, Booking_ID) VALUES (?, ?)");
                $instructor_stmt->bind_param("ii", $instructor_id, $booking_id);
                $instructor_stmt->execute();
                
                $success = true;
            }
        } else {
            $errors[] = "Selected lab not found.";
        }
    }
} catch (Exception $e) {
    $errors[] = "An error occurred while processing your booking.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Booking Confirmed' : 'Booking Failed'; ?> - Lab Booking System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .container {
            position: relative;
            z-index: 10;
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .success-header {
            background: linear-gradient(135deg, #10b981, #047857);
            padding: 50px 40px;
            text-align: center;
            color: white;
        }

        .error-header {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            padding: 50px 40px;
            text-align: center;
            color: white;
        }

        .success-icon, .error-icon {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            margin: 0 auto 30px;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .header-title {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .header-subtitle {
            font-size: 18px;
            opacity: 0.9;
            font-weight: 500;
        }

        .content {
            padding: 50px 40px;
        }

        .booking-details {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            padding: 32px;
            border-radius: 20px;
            margin-bottom: 40px;
            border: 1px solid #e2e8f0;
        }

        .detail-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .detail-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .detail-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .booking-id {
            font-size: 20px;
            font-weight: 800;
            color: #6366f1;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
        }

        .error-message {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: #dc2626;
            padding: 24px;
            border-radius: 16px;
            margin-bottom: 32px;
            border: 1px solid #fecaca;
            font-weight: 500;
        }

        .actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 32px;
            border: none;
            border-radius: 14px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 180px;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 45px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 45px rgba(100, 116, 139, 0.4);
        }

        @media (max-width: 768px) {
            .container {
                max-width: 95vw;
                margin: 10px;
            }

            .success-header, .error-header {
                padding: 40px 30px;
            }

            .content {
                padding: 40px 30px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <!-- Success State -->
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1 class="header-title">Booking Confirmed!</h1>
                <p class="header-subtitle">Your lab reservation has been successfully submitted</p>
            </div>
            
            <div class="content">
                <div class="booking-details">
                    <div class="detail-title">
                        <i class="fas fa-calendar-check"></i>
                        Booking Summary
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Booking ID</div>
                            <div class="detail-value booking-id">#<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Instructor</div>
                            <div class="detail-value"><?php echo htmlspecialchars($instructor_name); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Laboratory</div>
                            <div class="detail-value"><?php echo htmlspecialchars($lab_name); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Date</div>
                            <div class="detail-value"><?php echo date('F j, Y', strtotime($date)); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Time Slot</div>
                            <div class="detail-value"><?php echo htmlspecialchars($time_slot); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <span class="status-badge">
                                    <i class="fas fa-hourglass-half"></i>
                                    Pending Approval
                                </span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Submitted</div>
                            <div class="detail-value"><?php echo date('M j, Y \a\t g:i A'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <a href="view_bookings.php" class="btn btn-primary">
                        <i class="fas fa-list"></i>
                        View My Bookings
                    </a>
                    <a href="book_lab_form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Book Another Lab
                    </a>
                    <a href="../index.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- Error State -->
            <div class="error-header">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h1 class="header-title">Booking Failed</h1>
                <p class="header-subtitle">Unable to process your booking request</p>
            </div>
            
            <div class="content">
                <div class="error-message">
                    <strong>Error Details:</strong><br>
                    <?php echo implode('<br>', $errors); ?>
                </div>

                <div class="actions">
                    <a href="book_lab_form.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        Try Again
                    </a>
                    <a href="../index.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
