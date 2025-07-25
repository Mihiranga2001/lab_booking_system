<?php
session_start();
include '../db_connect.php';

// Only allow instructors
if ($_SESSION['role'] !== 'Instructor') {
    echo "Access denied.";
    exit;
}

// Get the schedule ID from the URL
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "No schedule selected.";
    exit;
}

// Fetch the current schedule data
$sql = "SELECT * FROM Lab_schedule WHERE Schedule_ID=?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();

if (!$schedule) {
    echo "<div class='error'>Schedule not found.</div>";
    exit;
}

$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lab_name = $_POST['lab_name'];
    $date = $_POST['date'];
    $time_slot = $_POST['time_slot'];
    $group_no = $_POST['group_no'];

    $sql = "UPDATE Lab_schedule SET Lab_Name=?, `Date`=?, Time_slot=?, Group_No=? WHERE Schedule_ID=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $error_message = "Prepare failed: " . htmlspecialchars($conn->error);
    } else {
        $stmt->bind_param("ssssi", $lab_name, $date, $time_slot, $group_no, $id);

        if ($stmt->execute()) {
            $success_message = "Schedule updated successfully!";
            // Refresh the schedule data
            $sql = "SELECT * FROM Lab_schedule WHERE Schedule_ID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $schedule = $result->fetch_assoc();
        } else {
            $error_message = "Error updating schedule.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lab Schedule - Academic Portal</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            background: #fff;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .form-control:hover {
            border-color: #bdc3c7;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-bottom: 15px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
            box-shadow: 0 8px 15px rgba(149, 165, 166, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(149, 165, 166, 0.4);
            text-decoration: none;
            color: white;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease-out;
        }

        .alert-error {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            border: none;
        }

        .alert-success {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
            border: none;
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .schedule-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #667eea;
        }

        .schedule-info h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .schedule-info p {
            color: #6c757d;
            margin: 5px 0;
            font-size: 0.9rem;
        }

        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .form-control {
                padding: 12px 12px 12px 40px;
            }
            
            .btn {
                padding: 14px;
                font-size: 1rem;
            }
        }

        /* Custom select styling for better consistency */
        select.form-control {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            appearance: none;
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-edit"></i> Edit Lab Schedule</h1>
            <p>Update laboratory session schedule</p>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="schedule-info">
            <h3><i class="fas fa-info-circle"></i> Current Schedule Details</h3>
            <p><strong>Schedule ID:</strong> <?php echo htmlspecialchars($schedule['Schedule_ID']); ?></p>
        </div>

        <form method="post" novalidate>
            <div class="form-group">
                <label for="lab_name">Laboratory Name</label>
                <div class="input-wrapper">
                    <i class="fas fa-flask"></i>
                    <input type="text" id="lab_name" name="lab_name" class="form-control" 
                           value="<?php echo htmlspecialchars($schedule['Lab_Name']); ?>"
                           placeholder="Enter laboratory name" required>
                </div>
            </div>

            <div class="form-group">
                <label for="date">Schedule Date</label>
                <div class="input-wrapper">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" id="date" name="date" class="form-control" 
                           value="<?php echo htmlspecialchars($schedule['Date']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="time_slot">Time Slot</label>
                <div class="input-wrapper">
                    <i class="fas fa-clock"></i>
                    <input type="text" id="time_slot" name="time_slot" class="form-control" 
                           value="<?php echo htmlspecialchars($schedule['Time_slot']); ?>"
                           placeholder="e.g., 9:00 AM - 11:00 AM" required>
                </div>
            </div>

            <div class="form-group">
                <label for="group_no">Group Number</label>
                <div class="input-wrapper">
                    <i class="fas fa-users"></i>
                    <input type="text" id="group_no" name="group_no" class="form-control" 
                           value="<?php echo htmlspecialchars($schedule['Group_No']); ?>"
                           placeholder="Enter group number" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Schedule
            </button>
        </form>

        <a href="view_schedule.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Schedule
        </a>
    </div>

    <script>
        // Add some interactive feedback
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.parentElement.style.transform = 'scale(1)';
                });
            });

            // Set minimum date to today for future scheduling
            const dateInput = document.getElementById('date');
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);

            // Auto-hide success message after 5 seconds
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.opacity = '0';
                    successAlert.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        successAlert.remove();
                    }, 300);
                }, 5000);
            }
        });
    </script>
</body>
</html>