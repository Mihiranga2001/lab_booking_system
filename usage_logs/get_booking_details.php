<?php
session_start();
include '../db_connect.php';

header('Content-Type: application/json');

// Access control
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Lecture_in_charge') {
    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);
    exit;
}

try {
    // Simplified query that works with basic table structure
    $sql = "SELECT 
                lb.Booking_ID,
                lb.Lab_Name,
                lb.Request_Date,
                lb.Request_Time_Slot,
                lb.Status,
                
                -- Instructor Information
                i.Name as Instructor_Name,
                i.Instructor_ID,
                i.Email as Instructor_Email,
                
                -- Booking relationship
                ibb.Instructor_ID as Booking_Instructor_ID
                
            FROM Lab_Booking lb
            INNER JOIN Instructor_Book_Booking ibb ON lb.Booking_ID = ibb.Booking_ID
            INNER JOIN instructor i ON ibb.Instructor_ID = i.Instructor_ID
            ORDER BY lb.Booking_ID DESC
            LIMIT 50";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $data = [];
    $html = '';
    
    if ($result->num_rows > 0) {
        $html .= '<table class="styled-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> Booking ID</th>
                            <th><i class="fas fa-flask"></i> Laboratory</th>
                            <th><i class="fas fa-user-tie"></i> Instructor</th>
                            <th><i class="fas fa-calendar"></i> Date</th>
                            <th><i class="fas fa-clock"></i> Time Slot</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
            
            $status = $row['Status'] ?? 'Pending';
            $status_class = 'status-' . strtolower($status);
            
            // Status icon
            $status_icon = '';
            switch($status) {
                case 'Approved':
                    $status_icon = '<i class="fas fa-check-circle"></i>';
                    break;
                case 'Rejected':
                    $status_icon = '<i class="fas fa-times-circle"></i>';
                    break;
                default:
                    $status_icon = '<i class="fas fa-hourglass-half"></i>';
                    $status = 'Pending';
            }
            
            $html .= '<tr>
                        <td><span class="booking-id">#' . str_pad($row['Booking_ID'], 6, '0', STR_PAD_LEFT) . '</span></td>
                        <td><strong>' . htmlspecialchars($row['Lab_Name']) . '</strong></td>
                        <td>
                            <div class="instructor-info">
                                <div class="instructor-name">' . htmlspecialchars($row['Instructor_Name']) . '</div>
                                <div class="instructor-id">ID: ' . htmlspecialchars($row['Instructor_ID']) . '</div>
                            </div>
                        </td>
                        <td>' . date('M d, Y', strtotime($row['Request_Date'])) . '</td>
                        <td>' . htmlspecialchars($row['Request_Time_Slot']) . '</td>
                        <td>
                            <span class="status-badge ' . $status_class . '">
                                ' . $status_icon . ' ' . $status . '
                            </span>
                        </td>
                      </tr>';
        }
        
        $html .= '</tbody></table>';
    } else {
        $html = '<div class="no-logs">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Booking Records Found</h3>
                    <p>No laboratory booking records available at the moment.</p>
                 </div>';
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'html' => $html,
        'count' => count($data),
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => time()
    ]);
}

$conn->close();
?>
