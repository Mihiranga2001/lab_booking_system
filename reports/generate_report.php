<?php
session_start();
include '../db_connect.php';

if (!in_array($_SESSION['role'], ['Lecture_in_charge', 'Lab_TO'])) {
    echo "<div class='container'><div class='alert alert-warning'>Access denied.</div></div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Usage Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Reset and base styling */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
            padding: 30px;
        }
        .container {
            background: #fff;
            padding: 36px 28px 32px 28px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.10);
            max-width: 700px;
            margin: 40px auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 32px;
            color: #222;
            font-size: 2rem;
            letter-spacing: 1px;
        }
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto 28px auto;
            font-size: 1rem;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .styled-table thead {
            background: linear-gradient(135deg, #007BFF, #0056b3);
            color: #fff;
        }
        .styled-table th, .styled-table td {
            padding: 15px 12px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        .styled-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .styled-table tbody tr:hover {
            background-color: #e8f4f8;
            transition: background 0.2s;
        }
        .button {
            display: inline-block;
            margin-top: 18px;
            padding: 12px 28px;
            background: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .button:hover {
            background: #0056b3;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin-bottom: 24px;
            border-radius: 6px;
            text-align: center;
        }
        @media (max-width: 600px) {
            .container { padding: 12px 4px; }
            .styled-table th, .styled-table td { padding: 8px 4px; font-size: 0.95rem; }
            h2 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Lab Usage Report</h2>
        <?php
        $sql = "SELECT Lab_Date,
                       COUNT(*) AS Total_Logs,
                       SUM(CASE WHEN Complete_Status = 1 THEN 1 ELSE 0 END) AS Completed,
                       SUM(CASE WHEN Complete_Status = 0 THEN 1 ELSE 0 END) AS Pending
                FROM Usage_log
                GROUP BY Lab_Date
                ORDER BY Lab_Date DESC";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table class='styled-table'>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Logs</th>
                    <th>Completed</th>
                    <th>Pending</th>
                </tr>
            </thead>
            <tbody>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['Lab_Date']}</td>
                        <td>{$row['Total_Logs']}</td>
                        <td>{$row['Completed']}</td>
                        <td>{$row['Pending']}</td>
                      </tr>";
            }

            echo "</tbody></table>";
        } else {
            echo "<div class='alert-warning'>No usage data found.</div>";
        }
        ?>
        <div style="text-align:center; margin-top: 18px;">
    <a href="../index.php" class="button">Back to Home</a>
</div>

    </div>
</body>
</html>
