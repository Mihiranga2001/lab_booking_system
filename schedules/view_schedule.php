<?php 
session_start(); 
include '../db_connect.php';  

if (!in_array($_SESSION['role'], ['Student', 'Lecture_in_charge','Instructor'])) {     
    echo "<div class='container'><div class='alert alert-warning'>Access denied.</div></div>";     
    exit; 
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Schedule - Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            width: 95%;
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .section-title i {
            color: #4facfe;
            font-size: 1.5rem;
        }
        
        .stats-info {
            display: flex;
            gap: 20px;
            align-items: center;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #f8f9fa;
            border-radius: 20px;
        }
        
        .stat-item i {
            color: #4facfe;
        }
        
        .table-container {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            margin: 20px 0;
        }
        
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
            background-color: #fff;
        }
        
        .styled-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .styled-table th {
            padding: 20px 15px;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }
        
        .styled-table td {
            padding: 18px 15px;
            text-align: center;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
            font-size: 0.95rem;
        }
        
        .styled-table tbody tr {
            transition: all 0.3s ease;
        }
        
        .styled-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .styled-table tbody tr:hover {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .lab-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .date-badge {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .time-slot {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .group-badge {
            background: #fff3e0;
            color: #ef6c00;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-update {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-add {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
        }
        
        .btn-home {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(168, 237, 234, 0.4);
        }
        
        .button-bar {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .alert {
            padding: 20px;
            margin: 20px 0;
            border-radius: 15px;
            text-align: center;
            border: none;
            font-weight: 500;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            box-shadow: 0 4px 15px rgba(255, 243, 205, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #495057;
        }
        
        .empty-state p {
            font-size: 1.1rem;
        }
        
        /* Responsive Design */
        @media screen and (max-width: 1200px) {
            .container { 
                padding: 30px; 
                margin: 10px auto; 
            }
        }
        
        @media screen and (max-width: 768px) {
            .page-header h1 { font-size: 2rem; }
            .container { padding: 20px; }
            .section-header { flex-direction: column; gap: 15px; }
            .stats-info { flex-wrap: wrap; }
            .styled-table { font-size: 14px; }
            .styled-table th, .styled-table td { padding: 12px 8px; }
            .button-bar { flex-direction: column; }
            .btn { justify-content: center; }
        }
        
        @media screen and (max-width: 500px) {
            .table-container { 
                overflow-x: auto; 
            }
            .styled-table { 
                min-width: 600px;
                font-size: 12px; 
            }
            .styled-table th, .styled-table td { 
                padding: 10px 6px; 
            }
            .date-badge, .time-slot, .group-badge {
                font-size: 0.75rem;
                padding: 4px 8px;
            }
        }
        
        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #4facfe;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Scroll indicator */
        .scroll-indicator {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            transform-origin: left;
            transform: scaleX(0);
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="scroll-indicator"></div>
    
    <div class="page-header">
        <h1><i class="fas fa-flask"></i> Laboratory Management</h1>
        <p>Schedule Overview</p>
    </div>

    <div class="container">
        <?php 
        $sql = "SELECT * FROM Lab_schedule ORDER BY Date, Time_slot"; 
        $result = $conn->query($sql);  
        $total_schedules = $result->num_rows;
        ?>
        
        <div class="section-header">
            <div class="section-title">
                <i class="fas fa-calendar-alt"></i>
                Lab Schedule
            </div>
            <div class="stats-info">
                <div class="stat-item">
                    <i class="fas fa-list"></i>
                    <span><?php echo $total_schedules; ?> Total Schedules</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-user"></i>
                    <span><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
        </div>

        <?php 
        if ($result->num_rows > 0) {     
            echo '<div class="table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-flask"></i> Lab Name</th>
                                <th><i class="fas fa-calendar"></i> Date</th>
                                <th><i class="fas fa-clock"></i> Time Slot</th>
                                <th><i class="fas fa-users"></i> Group No</th>';
            // Only show "Action" column for Instructor
            if ($_SESSION['role'] === 'Instructor') {
                echo '<th><i class="fas fa-cogs"></i> Actions</th>';
            }
            echo        '</tr>
                        </thead>
                        <tbody>';      

            while ($row = $result->fetch_assoc()) {         
                echo "<tr>                 
                        <td><span class='lab-name'>" . htmlspecialchars($row['Lab_Name']) . "</span></td>                 
                        <td><span class='date-badge'>" . htmlspecialchars($row['Date']) . "</span></td>                 
                        <td><span class='time-slot'>" . htmlspecialchars($row['Time_slot']) . "</span></td>                 
                        <td><span class='group-badge'>Group " . htmlspecialchars($row['Group_No']) . "</span></td>";
                // Only show Update button for Instructor
                if ($_SESSION['role'] === 'Instructor') {
                    echo '<td>
                            <div class="action-buttons">
                                <a href="update_schedule.php?id=' . urlencode($row['Schedule_ID']) . '" class="btn btn-update">
                                    <i class="fas fa-edit"></i> Update
                                </a>
                            </div>
                          </td>';
                }
                echo '</tr>';     
            }      
            echo '</tbody></table></div>'; 
        } else {     
            echo '<div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Schedules Found</h3>
                    <p>There are currently no lab schedules available.</p>
                  </div>'; 
        } 
        ?>
        
        <div class="button-bar">
            <?php if ($_SESSION['role'] === 'Instructor') { ?>
                <a href="add_schedule.php" class="btn btn-add">
                    <i class="fas fa-plus-circle"></i> Add Lab Schedule
                </a>
            <?php } ?>
            <a href="../index.php" class="btn btn-home">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
    
    <script>
        // Scroll indicator
        window.addEventListener('scroll', () => {
            const indicator = document.querySelector('.scroll-indicator');
            const scrolled = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
            indicator.style.transform = `scaleX(${scrolled / 100})`;
        });
        
        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Add loading state to buttons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.classList.contains('btn-home')) {
                    this.innerHTML = '<div class="loading"></div> Loading...';
                }
            });
        });
    </script>
</body>
</html>