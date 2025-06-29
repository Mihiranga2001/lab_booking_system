<?php
session_start();
include '../db_connect.php';

if ($_SESSION['role'] !== 'Lab_TO') {
    echo "<div class='container'><div class='alert alert-warning'>Access denied.</div></div>";
    exit;
}

// Handle approval/rejection first
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $booking_id = (int)$_GET['booking_id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $status = 'Approved';
        $update_sql = "UPDATE Lab_Booking SET Status = 'Approved' WHERE Booking_ID = ?";
    } elseif ($action === 'reject') {
        $status = 'Rejected';
        $update_sql = "UPDATE Lab_Booking SET Status = 'Rejected' WHERE Booking_ID = ?";
    }
    
    if (isset($update_sql)) {
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $message = "Booking has been " . strtolower($status) . " successfully!";
            $alert_type = ($status === 'Approved') ? 'success' : 'info';
        }
    }
}

// Get all bookings (not just those with TO notifications)
$sql = "SELECT lb.Booking_ID, lb.Lab_Name, lb.Request_Date, lb.Request_Time_Slot, 
               lb.Status, i.Name AS InstructorName, ibb.Instructor_ID
        FROM Lab_Booking lb
        INNER JOIN Instructor_Book_Booking ibb ON lb.Booking_ID = ibb.Booking_ID
        INNER JOIN instructor i ON ibb.Instructor_ID = i.Instructor_ID
        WHERE lb.Status = 'Pending' OR lb.Status IS NULL
        ORDER BY lb.Booking_ID DESC";

$result = $conn->query($sql);

// Get statistics
$pending_sql = "SELECT COUNT(*) as count FROM Lab_Booking WHERE Status = 'Pending' OR Status IS NULL";
$pending_result = $conn->query($pending_sql);
$pending_count = $pending_result->fetch_assoc()['count'];

$approved_sql = "SELECT COUNT(*) as count FROM Lab_Booking WHERE Status = 'Approved'";
$approved_result = $conn->query($approved_sql);
$approved_count = $approved_result->fetch_assoc()['count'];

$rejected_sql = "SELECT COUNT(*) as count FROM Lab_Booking WHERE Status = 'Rejected'";
$rejected_result = $conn->query($rejected_sql);
$rejected_count = $rejected_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Booking Management | Technical Officer Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; line-height: 1.6; }
        .main-container { max-width: 1200px; margin: 0 auto; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15); overflow: hidden; animation: slideInUp 0.6s ease-out; }
        @keyframes slideInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 40px; text-align: center; position: relative; overflow: hidden; }
        .header h1 { font-size: 2.5rem; font-weight: 700; margin-bottom: 10px; position: relative; z-index: 2; }
        .header p { font-size: 1.1rem; opacity: 0.9; position: relative; z-index: 2; }
        .content { padding: 40px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: linear-gradient(135deg, #f8fafc, #e2e8f0); padding: 25px; border-radius: 15px; text-align: center; border: 1px solid rgba(99, 102, 241, 0.1); transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); }
        .stat-icon { font-size: 2.5rem; margin-bottom: 15px; background: linear-gradient(135deg, #4f46e5, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .stat-number { font-size: 2rem; font-weight: 700; color: #1e293b; margin-bottom: 5px; }
        .stat-label { color: #64748b; font-weight: 500; }
        .section-title { font-size: 1.8rem; font-weight: 600; color: #1e293b; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
        .section-title i { color: #4f46e5; }
        .alert { padding: 20px; border-radius: 12px; margin-bottom: 30px; border-left: 4px solid; font-weight: 500; animation: slideInRight 0.5s ease-out; }
        .alert-success { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; border-color: #10b981; }
        .alert-info { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e40af; border-color: #3b82f6; }
        .table-container { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); margin-bottom: 30px; }
        .styled-table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
        .styled-table thead { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; }
        .styled-table th { padding: 20px 15px; text-align: left; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.85rem; }
        .styled-table td { padding: 18px 15px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        .styled-table tbody tr { transition: all 0.3s ease; }
        .styled-table tbody tr:hover { background: linear-gradient(135deg, #f8fafc, #f1f5f9); transform: scale(1.01); }
        .booking-id { font-weight: 600; color: #4f46e5; background: rgba(79, 70, 229, 0.1); padding: 5px 10px; border-radius: 8px; display: inline-block; }
        .action-buttons { display: flex; gap: 10px; justify-content: center; }
        .action-link { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.85rem; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px; min-width: 100px; justify-content: center; }
        .approve { background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .approve:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4); }
        .reject { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
        .reject:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4); }
        .empty-state { text-align: center; padding: 60px 20px; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 15px; margin: 30px 0; }
        .empty-state i { font-size: 4rem; color: #cbd5e1; margin-bottom: 20px; }
        .btn-primary { display: inline-flex; align-items: center; gap: 10px; padding: 15px 30px; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 1rem; transition: all 0.3s ease; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4); }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <h1><i class="fas fa-flask"></i> Lab Booking Management</h1>
            <p>Technical Officer - Manage Laboratory Reservations</p>
        </div>

        <div class="content">
            <?php if (isset($message)): ?>
                <div class="alert alert-<?= $alert_type ?>">
                    <i class="fas fa-check-circle"></i> <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-number"><?= $pending_count ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number"><?= $approved_count ?></div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-number"><?= $rejected_count ?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>

            <h2 class="section-title">
                <i class="fas fa-list-check"></i>
                Pending Booking Requests
            </h2>

            <?php if ($result->num_rows > 0): ?>
                <div class="table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Laboratory</th>
                                <th>Date</th>
                                <th>Time Slot</th>
                                <th>Instructor</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['Lab_Name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['Request_Date'])) ?></td>
                                    <td><?= htmlspecialchars($row['Request_Time_Slot']) ?></td>
                                    <td><?= htmlspecialchars($row['InstructorName']) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?action=approve&booking_id=<?= $row['Booking_ID'] ?>" 
                                               class="action-link approve" 
                                               onclick="return confirm('Approve this booking?')">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="?action=reject&booking_id=<?= $row['Booking_ID'] ?>" 
                                               class="action-link reject"
                                               onclick="return confirm('Reject this booking?')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-check"></i>
                    <h3>All Caught Up!</h3>
                    <p>There are no pending booking requests at the moment.</p>
                </div>
            <?php endif; ?>

            <div style="text-align: center; padding-top: 20px;">
                <a href="../index.php" class="btn-primary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
