<?php
session_start();
include '../db_connect.php';

if ($_SESSION['role'] !== 'Instructor') {
    echo "Access denied.";
    exit;
}

$instructor_id = $_SESSION['user_id'];
$instructor_name = $_SESSION['name'] ?? 'Instructor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Lab Bookings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            max-width: 1000px;
            margin: 0 auto;
            overflow: hidden;
        }

        .content-wrapper {
            padding: 40px;
        }

        .stats-bar {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 15px;
            color: white;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .bookings-section {
            margin-top: 30px;
        }

        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            background: white;
        }

        .styled-table thead {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .styled-table th {
            padding: 20px 15px;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .styled-table td {
            padding: 18px 15px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .styled-table tbody tr {
            transition: all 0.3s ease;
        }

        .styled-table tbody tr:hover {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .booking-id {
            font-weight: 600;
            color: #4facfe;
            background: rgba(79, 172, 254, 0.1);
            padding: 5px 10px;
            border-radius: 8px;
        }

        .lab-name {
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lab-icon {
            color: #4facfe;
        }

        .date-cell {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .date-day {
            font-weight: bold;
            font-size: 1.1rem;
            color: #333;
        }

        .date-month {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
        }

        .time-slot {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-block;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-approved {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
        }

        .status-pending {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
        }

        .status-rejected {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
        }

        .no-bookings {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-bookings-icon {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-bookings h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #333;
        }

        .no-bookings p {
            font-size: 1.1rem;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .auto-refresh {
            background: #e3f2fd;
            color: #1976d2;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .content-wrapper {
                padding: 20px;
            }

            .stats-bar {
                flex-direction: column;
                gap: 15px;
            }

            .styled-table {
                font-size: 0.9rem;
            }

            .styled-table th,
            .styled-table td {
                padding: 12px 8px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 250px;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .styled-table th:nth-child(3),
            .styled-table td:nth-child(3) {
                display: none;
            }

            .time-slot {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-calendar-check"></i> My Lab Bookings</h1>
        <p>Instructor: <?= htmlspecialchars($instructor_name) ?> | Manage and track your laboratory reservations</p>
    </div>

    <div class="container">
        <div class="content-wrapper">
            <div class="auto-refresh">
                <i class="fas fa-sync-alt"></i> Page auto-refreshes every 30 seconds to show latest status updates
            </div>

            <?php
            // Get booking statistics using CORRECTED QUERIES
            $total_sql = "SELECT COUNT(*) as total FROM Lab_Booking lb
                         INNER JOIN Instructor_Book_Booking ibb ON lb.Booking_ID = ibb.Booking_ID
                         WHERE ibb.Instructor_ID = ?";
            $total_stmt = $conn->prepare($total_sql);
            $total_stmt->bind_param("i", $instructor_id);
            $total_stmt->execute();
            $total_bookings = $total_stmt->get_result()->fetch_assoc()['total'];

            $approved_sql = "SELECT COUNT(*) as approved FROM Lab_Booking lb
                            INNER JOIN Instructor_Book_Booking ibb ON lb.Booking_ID = ibb.Booking_ID
                            WHERE ibb.Instructor_ID = ? AND lb.Status = 'Approved'";
            $approved_stmt = $conn->prepare($approved_sql);
            $approved_stmt->bind_param("i", $instructor_id);
            $approved_stmt->execute();
            $approved_count = $approved_stmt->get_result()->fetch_assoc()['approved'];

            $pending_sql = "SELECT COUNT(*) as pending FROM Lab_Booking lb
                           INNER JOIN Instructor_Book_Booking ibb ON lb.Booking_ID = ibb.Booking_ID
                           WHERE ibb.Instructor_ID = ? AND (lb.Status = 'Pending' OR lb.Status IS NULL)";
            $pending_stmt = $conn->prepare($pending_sql);
            $pending_stmt->bind_param("i", $instructor_id);
            $pending_stmt->execute();
            $pending_count = $pending_stmt->get_result()->fetch_assoc()['pending'];

            $rejected_sql = "SELECT COUNT(*) as rejected FROM Lab_Booking lb
                            INNER JOIN Instructor_Book_Booking ibb ON lb.Booking_ID = ibb.Booking_ID
                            WHERE ibb.Instructor_ID = ? AND lb.Status = 'Rejected'";
            $rejected_stmt = $conn->prepare($rejected_sql);
            $rejected_stmt->bind_param("i", $instructor_id);
            $rejected_stmt->execute();
            $rejected_count = $rejected_stmt->get_result()->fetch_assoc()['rejected'];
            ?>

            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-number"><?= $total_bookings ?></span>
                    <span class="stat-label">Total Bookings</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= $approved_count ?></span>
                    <span class="stat-label">Approved</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= $pending_count ?></span>
                    <span class="stat-label">Pending</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= $rejected_count ?></span>
                    <span class="stat-label">Rejected</span>
                </div>
            </div>

            <div class="bookings-section">
                <h2 class="section-title">
                    <i class="fas fa-list"></i>
                    All My Bookings
                </h2>

                <?php
                // Get all bookings with CORRECTED QUERY
                $sql = "SELECT lb.Booking_ID, lb.Lab_Name, lb.Request_Date, lb.Request_Time_Slot, lb.Status
                        FROM Lab_Booking lb
                        INNER JOIN Instructor_Book_Booking ibb ON lb.Booking_ID = ibb.Booking_ID
                        WHERE ibb.Instructor_ID = ?
                        ORDER BY lb.Booking_ID DESC";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $instructor_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<table class='styled-table'>
                    <thead>
                    <tr>
                        <th><i class='fas fa-hashtag'></i> Booking ID</th>
                        <th><i class='fas fa-flask'></i> Laboratory</th>
                        <th><i class='fas fa-calendar'></i> Date</th>
                        <th><i class='fas fa-clock'></i> Time Slot</th>
                        <th><i class='fas fa-info-circle'></i> Status</th>
                    </tr>
                    </thead>
                    <tbody>";

                    while ($row = $result->fetch_assoc()) {
                        $status = $row['Status'] ?? 'Pending';
                        $status_class = strtolower($status);
                        
                        // Format date
                        $date = new DateTime($row['Request_Date']);
                        $day = $date->format('d');
                        $month = $date->format('M');
                        
                        // Status icon
                        $status_icon = '';
                        switch($status) {
                            case 'Approved':
                                $status_icon = '<i class="fas fa-check"></i>';
                                break;
                            case 'Rejected':
                                $status_icon = '<i class="fas fa-times"></i>';
                                break;
                            default:
                                $status_icon = '<i class="fas fa-hourglass-half"></i>';
                                $status = 'Pending';
                        }

                        echo "<tr>
                                <td>
                                    <span class='booking-id'>#" . str_pad($row['Booking_ID'], 6, '0', STR_PAD_LEFT) . "</span>
                                </td>
                                <td>
                                    <div class='lab-name'>
                                        <i class='fas fa-microscope lab-icon'></i>
                                        " . htmlspecialchars($row['Lab_Name']) . "
                                    </div>
                                </td>
                                <td>
                                    <div class='date-cell'>
                                        <span class='date-day'>$day</span>
                                        <span class='date-month'>$month</span>
                                    </div>
                                </td>
                                <td>
                                    <span class='time-slot'>" . htmlspecialchars($row['Request_Time_Slot']) . "</span>
                                </td>
                                <td>
                                    <span class='status-badge status-$status_class'>
                                        $status_icon $status
                                    </span>
                                </td>
                              </tr>";
                    }

                    echo "</tbody></table>";

                } else {
                    echo "<div class='no-bookings'>
                            <div class='no-bookings-icon'>
                                <i class='fas fa-calendar-times'></i>
                            </div>
                            <h3>No Lab Bookings Found</h3>
                            <p>You haven't made any laboratory reservations yet.</p>
                          </div>";
                }

                $conn->close();
                ?>
            </div>

            <div class="action-buttons">
                <a href="book_lab_form.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Booking
                </a>
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh page every 30 seconds to show latest status
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
