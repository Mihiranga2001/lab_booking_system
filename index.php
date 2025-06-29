<?php
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header("Location: login/login_form.html");
    exit;
}
$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'User';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
$date = date('l, F j, Y');
$time = date('g:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Lab Booking System - Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f7fa; margin: 0; color: #222; }
        .sidebar { width: 250px; background: #212c3a; color: #fff; position: fixed; top: 0; left: 0; bottom: 0; display: flex; flex-direction: column; z-index: 100; }
        .sidebar .logo { font-weight: 700; font-size: 1.5rem; background: #3b82f6; color: #fff; border-radius: 8px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; margin: 24px auto 8px auto; }
        .sidebar .user-card { background: #263448; border-radius: 12px; padding: 16px; margin: 0 16px 24px 16px; text-align: center; }
        .sidebar .user-card .name { font-weight: 600; margin-bottom: 2px; }
        .sidebar .user-card .role { font-size: 0.95rem; color: #b0b9c6; }
        .sidebar nav { flex: 1; }
        .sidebar nav ul { list-style: none; padding: 0; margin: 0; }
        .sidebar nav ul li { margin: 0; }
        .sidebar nav ul li a { display: flex; align-items: center; gap: 12px; color: #b0b9c6; text-decoration: none; padding: 14px 24px; font-weight: 500; font-size: 1rem; transition: background 0.2s, color 0.2s; border-left: 4px solid transparent; }
        .sidebar nav ul li a.active, .sidebar nav ul li a:hover { background: #253047; color: #fff; border-left: 4px solid #3b82f6; }
        .sidebar .logout-btn { margin: 24px 16px 24px 16px; background: #ef4444; color: #fff; border: none; border-radius: 8px; padding: 12px 0; font-weight: 600; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: background 0.2s; }
        .sidebar .logout-btn:hover { background: #b91c1c; }
        .main-content { margin-left: 250px; padding: 32px 40px; min-height: 100vh; background: #f5f7fa; }
        .header-row { display: flex; justify-content: space-between; align-items: center; }
        .header-row h1 { font-size: 2rem; font-weight: 700; margin-bottom: 4px; }
        .header-row .date { color: #64748b; font-size: 1rem; text-align: right; }
        .quick-actions { margin-top: 32px; }
        .quick-actions-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 18px; color: #222; }
        .cards-row { display: flex; gap: 24px; flex-wrap: wrap; }
        .action-card { flex: 1 1 260px; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(60,72,88,0.06); padding: 28px 24px; min-width: 260px; max-width: 340px; display: flex; flex-direction: column; align-items: flex-start; transition: box-shadow 0.2s, transform 0.2s; border: 1px solid #e5e7eb; }
        .action-card:hover { box-shadow: 0 8px 32px rgba(59,130,246,0.10); transform: translateY(-4px) scale(1.02); }
        .action-card .icon { font-size: 2.2rem; margin-bottom: 18px; color: #3b82f6; }
        .action-card.orange .icon { color: #f59e0b; }
        .action-card.green .icon { color: #10b981; }
        .action-card h2 { font-size: 1.15rem; font-weight: 700; margin-bottom: 8px; color: #222; }
        .action-card p { font-size: 1rem; color: #64748b; margin-bottom: 16px; }
        .action-card ul { margin: 0 0 0 18px; padding: 0; color: #10b981; font-size: 0.98rem; }
        @media (max-width: 900px) { .main-content { padding: 24px 8px; } .cards-row { flex-direction: column; gap: 18px; } }
        @media (max-width: 600px) { .sidebar { width: 100%; position: static; min-height: 0; } .main-content { margin-left: 0; padding: 16px 2vw; } }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo">UJ</div>
    <div class="user-card">
        <div class="name"><?= htmlspecialchars($name) ?></div>
        <div class="role"><?= htmlspecialchars($role) ?><br>ID: <?= htmlspecialchars($user_id) ?></div>
    </div>
    <nav>
        <ul>
            <li><a class="active" href="#">Overview</a></li>
            <?php if ($role === "Instructor"): ?>
                <li><a href="bookings/book_lab_form.php"><i class="fas fa-calendar-plus"></i>Book a Lab</a></li>
                <li><a href="bookings/view_bookings.php"><i class="fas fa-list"></i>My Bookings</a></li>
                <li><a href="schedules/view_schedule.php"><i class="fas fa-calendar-alt"></i>Lab Schedule</a></li>
            <?php elseif ($role === "Lab_TO"): ?>
                <li><a href="approval/to_approve_bookings.php"><i class="fas fa-check-circle"></i>Booking Requests</a></li>
                <li><a href="labs/view_labs.php"><i class="fas fa-flask"></i>Labs</a></li>
                <li><a href="labs/view_equipments.php"><i class="fas fa-tools"></i>Equipment</a></li>
            <?php elseif ($role === "Student"): ?>
                <li><a href="schedules/view_schedule.php"><i class="fas fa-calendar-alt"></i>Lab Schedule</a></li>
            <?php elseif ($role === "Lecture_in_charge"): ?>
                <li><a href="schedules/view_schedule.php"><i class="fas fa-calendar-alt"></i>Lab Schedule</a></li>
                <li><a href="usage_logs/view_usage_log.php"><i class="fas fa-chart-line"></i>Usage Report</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>
<div class="main-content">
    <div class="header-row">
        <div>
            <h1>
                <?php if ($role === "Instructor"): ?>
                    Welcome back, <?= htmlspecialchars($name) ?>!
                <?php elseif ($role === "Lab_TO"): ?>
                    Welcome back, <?= htmlspecialchars($name) ?>!
                <?php elseif ($role === "Student"): ?>
                    Welcome, <?= htmlspecialchars($name) ?>!
                <?php elseif ($role === "Lecture_in_charge"): ?>
                    Welcome, <?= htmlspecialchars($name) ?>!
                <?php endif; ?>
            </h1>
            <div style="color:#64748b;">
                <?php if ($role === "Instructor"): ?>
                    Track your lab booking status and approvals
                <?php elseif ($role === "Lab_TO"): ?>
                    Manage lab requests, check labs and equipment
                <?php elseif ($role === "Student"): ?>
                    View your lab schedule
                <?php elseif ($role === "Lecture_in_charge"): ?>
                    View lab schedule and usage reports
                <?php endif; ?>
            </div>
        </div>
        <div class="date"><?= $date ?><br><span style="font-size:0.95rem;"><?= $time ?></span></div>
    </div>
    <div class="quick-actions">
        <div class="quick-actions-title">Quick Actions</div>
        <div class="cards-row">
            <?php if ($role === "Instructor"): ?>
                <div class="action-card">
                    <div class="icon"><i class="fas fa-calendar-plus"></i></div>
                    <h2>Book a Lab</h2>
                    <p>Reserve lab space for your classes</p>
                    <ul>
                        <li>Select available time slots</li>
                        <li>Choose equipment requirements</li>
                        <li>Instant booking submission</li>
                    </ul>
                </div>
                <div class="action-card green">
                    <div class="icon"><i class="fas fa-list"></i></div>
                    <h2>Manage Bookings</h2>
                    <p>View and modify your reservations</p>
                    <ul>
                        <li>View booking status</li>
                        <li>Modify or cancel bookings</li>
                        <li>Track approval progress</li>
                    </ul>
                </div>
                <div class="action-card orange">
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    <h2>Lab Schedule</h2>
                    <p>Check availability and timing</p>
                    <ul>
                        <li>Real-time availability</li>
                        <li>Weekly/monthly views</li>
                        <li>Filter by lab type</li>
                    </ul>
                </div>
            <?php elseif ($role === "Lab_TO"): ?>
                <div class="action-card">
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                    <h2>Review Requests</h2>
                    <p>See all pending booking requests</p>
                    <ul>
                        <li>Approve or reject bookings</li>
                        <li>See instructor details</li>
                    </ul>
                </div>
                <div class="action-card green">
                    <div class="icon"><i class="fas fa-flask"></i></div>
                    <h2>Check Labs</h2>
                    <p>Review lab status and details</p>
                    <ul>
                        <li>Lab availability</li>
                        <li>Lab details</li>
                    </ul>
                </div>
                <div class="action-card orange">
                    <div class="icon"><i class="fas fa-tools"></i></div>
                    <h2>Check Equipment</h2>
                    <p>View and manage lab equipment</p>
                    <ul>
                        <li>Equipment status</li>
                        <li>Maintenance info</li>
                    </ul>
                </div>
            <?php elseif ($role === "Student"): ?>
                <div class="action-card">
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    <h2>Lab Schedule</h2>
                    <p>Check your lab schedule</p>
                    <ul>
                        <li>View all lab sessions</li>
                        <li>Real-time updates</li>
                    </ul>
                </div>
            <?php elseif ($role === "Lecture_in_charge"): ?>
                <div class="action-card">
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    <h2>Lab Schedule</h2>
                    <p>Check all lab bookings and sessions</p>
                    <ul>
                        <li>Weekly/monthly calendar</li>
                        <li>Lab availability</li>
                    </ul>
                </div>
                <div class="action-card green">
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                    <h2>Usage Report</h2>
                    <p>Analyze lab usage statistics</p>
                    <ul>
                        <li>Generate usage reports</li>
                        <li>Export data</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
