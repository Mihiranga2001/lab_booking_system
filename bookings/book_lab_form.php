<?php
session_start();
include '../db_connect.php';

if ($_SESSION['role'] !== 'Instructor') {
    echo "Access denied.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Lab - Laboratory Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            max-width: 600px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            color: #2c3e50;
            font-size: 2.2em;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 15px 18px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            background: white;
            transition: all 0.3s ease;
            color: #2c3e50;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .form-group select {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
            appearance: none;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            margin-top: 15px;
            padding: 16px;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            width: 100%;
            font-size: 1.1em;
        }

        .back-link:hover {
            background: linear-gradient(135deg, #7f8c8d 0%, #95a5a6 100%);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(127, 140, 141, 0.4);
        }

        .lab-option {
            padding: 12px;
            border-radius: 8px;
            margin: 5px 0;
        }

        .no-labs {
            color: #e74c3c;
            font-style: italic;
        }

        .time-hint {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 5px;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }
            
            .header h2 {
                font-size: 1.8em;
                flex-direction: column;
                gap: 10px;
            }
        }

        /* Loading animation for form submission */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .loading .submit-btn {
            background: #95a5a6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>
                <i class="fas fa-flask"></i>
                Book a Laboratory
            </h2>
            <p>Reserve your lab session for upcoming classes</p>
        </div>

        <form action="submit_booking.php" method="POST" id="bookingForm">
            <div class="form-group">
                <label for="lab_id">
                    <i class="fas fa-building"></i>
                    Select Laboratory
                </label>
                <select name="lab_id" id="lab_id" required>
    <option value="">Choose a laboratory...</option>
    <?php
    $result = $conn->query("SELECT * FROM Lab");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['Lab_ID']}' class='lab-option'>";
            echo "{$row['Lab_Name']} (Capacity: {$row['Capacity']} students)";
            echo "</option>";
        }
    } else {
        echo "<option disabled class='no-labs'>No laboratories found</option>";
    }
    ?>
</select>

            </div>

            <div class="form-group">
                <label for="date">
                    <i class="fas fa-calendar-alt"></i>
                    Booking Date
                </label>
                <input type="date" name="date" id="date" required min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="time_slot">
                    <i class="fas fa-clock"></i>
                    Time Slot
                </label>
                <input type="text" name="time_slot" id="time_slot" placeholder="e.g., 9:00 AM - 12:00 PM" required>
                <div class="time-hint">
                    Please specify the start and end time for your lab session
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i>
                Submit Booking Request
            </button>
        </form>

        <a href="../index.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Home
        </a>
    </div>

    <script>
        // Set minimum date to today
        document.getElementById('date').setAttribute('min', new Date().toISOString().split('T')[0]);
        
        // Add loading state on form submission
        document.getElementById('bookingForm').addEventListener('submit', function() {
            this.classList.add('loading');
        });

        // Auto-format time input
        document.getElementById('time_slot').addEventListener('blur', function() {
            let value = this.value.trim();
            if (value && !value.includes(' - ')) {
                // Basic time format validation could be added here
            }
        });
    </script>
</body>
</html>