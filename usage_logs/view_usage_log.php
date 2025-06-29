<?php
session_start();
include '../db_connect.php';

// Access control: Only Lecture_in_charge allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Lecture_in_charge') {
    echo "<div class='container'><div class='alert alert-warning'>Access denied.</div></div>";
    exit;
}

$lic_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Booking Monitor - Real Time</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .main-container {
            max-width: 1600px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
        }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            min-height: 200px;
        }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .styled-table thead {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
        }

        .styled-table th {
            padding: 14px 10px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-size: 11px;
        }

        .styled-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .styled-table tbody tr {
            transition: all 0.3s ease;
        }

        .styled-table tbody tr:hover {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 16px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .status-approved {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
        }

        .status-pending {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
        }

        .status-rejected {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }

        .booking-id {
            font-weight: 700;
            color: #4f46e5;
            background: rgba(79, 70, 229, 0.1);
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 11px;
        }

        .instructor-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .instructor-name {
            font-weight: 600;
            color: #1e293b;
        }

        .instructor-id {
            font-size: 11px;
            color: #64748b;
        }

        .loading {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
            font-size: 16px;
        }

        .loading i {
            font-size: 2rem;
            margin-bottom: 16px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .no-logs {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .no-logs i {
            font-size: 3rem;
            margin-bottom: 16px;
            color: #cbd5e1;
        }

        .new-entry {
            animation: highlightNew 3s ease-in-out;
        }

        @keyframes highlightNew {
            0% { background-color: #fef3c7; }
            100% { background-color: transparent; }
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(79, 70, 229, 0.4);
        }

        .error-message {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #ef4444;
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }

            .styled-table {
                font-size: 11px;
            }

            .styled-table th, .styled-table td {
                padding: 8px 6px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Lab Booking Monitor</h1>
            <p>Real-Time Laboratory Booking & Approval System</p>
        </div>

        <div class="content">
            <!-- Table Container -->
            <div class="table-container">
                <div id="table-container">
                    <div class="loading">
                        <i class="fas fa-spinner"></i>
                        <div>Loading booking data...</div>
                    </div>
                </div>
            </div>

            <!-- Home Button -->
            <div style="text-align: center;">
                <a href="../index.php" class="btn-home">
                    <i class="fas fa-home"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        let refreshInterval;
        let currentData = '';

        $(document).ready(function() {
            // Start auto-refresh immediately
            loadUsageLog();
            startAutoRefresh();
        });

        function startAutoRefresh() {
            // Auto-refresh every 5 seconds
            refreshInterval = setInterval(loadUsageLog, 5000);
        }

        function loadUsageLog() {
            $.ajax({
                url: 'get_booking_details.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        updateTable(response.data, response.html);
                    } else {
                        $('#table-container').html('<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Error: ' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', error);
                    $('#table-container').html('<div class="error-message"><i class="fas fa-exclamation-triangle"></i> Error loading data. Please refresh the page.</div>');
                }
            });
        }

        function updateTable(newData, newHtml) {
            const newDataString = JSON.stringify(newData);
            
            if (currentData !== newDataString) {
                $('#table-container').html(newHtml);
                
                // Highlight new entries
                if (currentData !== '') {
                    setTimeout(function() {
                        $('tbody tr').first().addClass('new-entry');
                    }, 100);
                }
                
                currentData = newDataString;
            }
        }
    </script>
</body>
</html>
