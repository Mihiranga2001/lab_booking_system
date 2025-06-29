<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Lab_TO') {
    header("Location: ../login/login_form.html");
    exit;
}

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Check if Lab_Availability table exists, create if not
$table_check = $conn->query("SHOW TABLES LIKE 'Lab_Availability'");
if ($table_check->num_rows == 0) {
    $create_table = "CREATE TABLE Lab_Availability (
        id INT AUTO_INCREMENT PRIMARY KEY,
        Lab_ID INT NOT NULL,
        availability_date DATE NOT NULL,
        is_available BOOLEAN DEFAULT TRUE,
        notes TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_lab_date (Lab_ID, availability_date)
    )";
    
    if (!$conn->query($create_table)) {
        die("Error creating Lab_Availability table: " . $conn->error);
    }
}

// Query to get labs with date-specific availability
$sql = "SELECT l.Lab_ID, l.Lab_Name, l.Capacity, l.Availability as general_availability,
               COALESCE(la.is_available, l.Availability) as date_availability,
               la.notes, la.updated_at,
               CASE WHEN la.is_available IS NOT NULL THEN 1 ELSE 0 END as has_date_override
        FROM Lab l
        LEFT JOIN Lab_Availability la ON l.Lab_ID = la.Lab_ID AND la.availability_date = ?
        ORDER BY l.Lab_ID";

$stmt = $conn->prepare($sql);

// Check if prepare failed
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $selected_date);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Date-Specific Lab Availability Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 16px; font-size: 14px; }
        .main-container { max-width: 1200px; margin: 0 auto; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border-radius: 20px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15); overflow: hidden; animation: slideInUp 0.8s ease-out; }
        @keyframes slideInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 32px; text-align: center; }
        .header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 8px; }
        .header p { font-size: 0.95rem; opacity: 0.9; }
        .date-picker-section { background: linear-gradient(135deg, #f8fafc, #e2e8f0); padding: 32px; border-bottom: 1px solid #e2e8f0; }
        .date-picker-container { max-width: 700px; margin: 0 auto; text-align: center; }
        .date-picker-title { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .date-picker-subtitle { color: #64748b; margin-bottom: 24px; font-size: 0.95rem; }
        .date-input-wrapper { display: flex; align-items: center; justify-content: center; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
        .date-input-group { display: flex; align-items: center; background: white; border: 2px solid #e2e8f0; border-radius: 12px; padding: 12px 20px; box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08); transition: all 0.3s ease; min-width: 240px; }
        .date-input-group:focus-within { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .date-input { border: none; outline: none; font-size: 0.95rem; font-weight: 600; color: #1e293b; background: transparent; flex: 1; }
        .view-btn { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; border: none; padding: 12px 24px; border-radius: 12px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px; }
        .view-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4); }
        .selected-date-banner { background: linear-gradient(135deg, #10b981, #047857); color: white; padding: 20px 32px; text-align: center; }
        .selected-date-text { font-size: 1.2rem; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .content { padding: 32px; }
        .info-banner { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e40af; padding: 16px; border-radius: 12px; margin-bottom: 24px; text-align: center; border-left: 4px solid #3b82f6; font-size: 0.9rem; }
        .success-banner { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; padding: 16px; border-radius: 12px; margin-bottom: 24px; text-align: center; border-left: 4px solid #10b981; font-size: 0.9rem; }
        .labs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 32px; }
        .lab-card { background: white; border-radius: 16px; box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08); overflow: hidden; transition: all 0.3s ease; border: 1px solid #e2e8f0; }
        .lab-card:hover { transform: translateY(-4px); box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12); }
        .lab-card-header { background: linear-gradient(135deg, #f8fafc, #e2e8f0); padding: 20px; border-bottom: 1px solid #e2e8f0; }
        .lab-title { font-size: 1.2rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
        .lab-id { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 3px 10px; border-radius: 10px; font-size: 0.75rem; font-weight: 600; }
        .override-indicator { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 3px 6px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; }
        .lab-body { padding: 20px; }
        .lab-info { display: grid; grid-template-columns: 1fr; gap: 12px; margin-bottom: 20px; }
        .info-item { display: flex; flex-direction: column; gap: 3px; }
        .info-label { font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
        .info-value { font-size: 0.95rem; font-weight: 600; color: #1e293b; }
        .availability-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 16px; font-size: 0.8rem; font-weight: 600; }
        .available { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; }
        .unavailable { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; }
        .toggle-button { width: 100%; padding: 10px 20px; border: none; border-radius: 10px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; }
        .toggle-enable { background: linear-gradient(135deg, #10b981, #047857); color: white; }
        .toggle-disable { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; }
        .toggle-button:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15); }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border: none; border-radius: 12px; font-weight: 600; font-size: 0.95rem; text-decoration: none; cursor: pointer; transition: all 0.3s ease; }
        .btn-primary { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; }
        .btn-secondary { background: linear-gradient(135deg, #64748b, #475569); color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2); }
        .btn-secondary:hover { background: linear-gradient(135deg, #475569, #334155); box-shadow: 0 8px 25px rgba(100, 116, 139, 0.4); }
        .action-buttons { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
        @media (max-width: 768px) { 
            body { padding: 12px; font-size: 13px; } 
            .labs-grid { grid-template-columns: 1fr; gap: 16px; } 
            .date-input-wrapper { flex-direction: column; } 
            .header { padding: 24px; } 
            .header h1 { font-size: 1.75rem; } 
            .content { padding: 24px; }
            .action-buttons { flex-direction: column; align-items: center; }
            .btn { width: 100%; max-width: 280px; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <h1><i class="fas fa-calendar-day"></i> Date-Specific Lab Availability</h1>
            <p>Manage lab availability for individual dates</p>
        </div>

        <div class="date-picker-section">
            <div class="date-picker-container">
                <h2 class="date-picker-title">
                    <i class="fas fa-calendar-alt"></i> Select Date
                </h2>
                <p class="date-picker-subtitle">Choose any date to manage lab availability for that specific day</p>
                
                <form method="GET" class="date-input-wrapper">
                    <div class="date-input-group">
                        <i class="fas fa-calendar" style="color: #4f46e5; font-size: 1rem; margin-right: 10px;"></i>
                        <input type="date" name="date" value="<?= htmlspecialchars($selected_date) ?>" class="date-input" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+2 years')) ?>">
                    </div>
                    <button type="submit" class="view-btn">
                        <i class="fas fa-search"></i> Load Date
                    </button>
                </form>
            </div>
        </div>

        <div class="selected-date-banner">
            <div class="selected-date-text">
                <i class="fas fa-calendar-check"></i>
                Managing: <?= date('l, F j, Y', strtotime($selected_date)) ?>
            </div>
        </div>

        <div class="content">
            <?php if (isset($_GET['updated'])): ?>
                <div class="success-banner">
                    <i class="fas fa-check-circle"></i>
                    <strong>Success!</strong> Lab "<?= htmlspecialchars($_GET['lab'] ?? 'Unknown') ?>" availability updated for <?= date('F j, Y', strtotime($selected_date)) ?> only.
                </div>
            <?php endif; ?>

            <div class="info-banner">
                <i class="fas fa-info-circle"></i>
                <strong>Date-Independent:</strong> Changes affect ONLY <?= date('M j, Y', strtotime($selected_date)) ?>. Other dates remain unchanged.
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="labs-grid">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <?php
                        $labID = htmlspecialchars($row['Lab_ID']);
                        $labName = htmlspecialchars($row['Lab_Name']);
                        $capacity = htmlspecialchars($row['Capacity']);
                        $dateAvailability = $row['date_availability'];
                        $hasOverride = $row['has_date_override'];
                        $toggleValue = $dateAvailability ? 0 : 1;
                        ?>
                        <div class="lab-card">
                            <div class="lab-card-header">
                                <div class="lab-title">
                                    <i class="fas fa-microscope"></i>
                                    <?= $labName ?>
                                    <span class="lab-id">ID: <?= $labID ?></span>
                                    <?php if ($hasOverride): ?>
                                        <span class="override-indicator">CUSTOM</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="lab-body">
                                <div class="lab-info">
                                    <div class="info-item">
                                        <div class="info-label">Capacity</div>
                                        <div class="info-value">
                                            <i class="fas fa-users"></i> <?= $capacity ?> Students
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label">Current Status</div>
                                        <div class="info-value">
                                            <span class="availability-badge <?= $dateAvailability ? 'available' : 'unavailable' ?>">
                                                <i class="fas <?= $dateAvailability ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                                <?= $dateAvailability ? 'Available' : 'Unavailable' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <a href="update_lab.php?lab_id=<?= $labID ?>&date=<?= $selected_date ?>&status=<?= $toggleValue ?>"
                                   class="toggle-button <?= $dateAvailability ? 'toggle-disable' : 'toggle-enable' ?>"
                                   onclick="return confirmDateToggle('<?= $labName ?>', '<?= date('F j, Y', strtotime($selected_date)) ?>', '<?= $toggleValue ?>');">
                                    <i class="fas <?= $dateAvailability ? 'fa-ban' : 'fa-check' ?>"></i>
                                    <?= $dateAvailability ? 'Make Unavailable' : 'Make Available' ?>
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 15px;">
                    <i class="fas fa-flask" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 20px;"></i>
                    <h3>No Labs Found</h3>
                    <p>No laboratory data found. Please add labs to the system.</p>
                </div>
            <?php endif; ?>

            <div style="text-align: center; padding-top: 32px; border-top: 1px solid #e2e8f0;">
                <div class="action-buttons">
                    <a href="../index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                    <a href="view_equipments.php<?= isset($_GET['date']) ? '?date=' . urlencode($_GET['date']) : '' ?>" class="btn btn-secondary">
                        <i class="fas fa-tools"></i> View Equipment
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDateToggle(labName, date, newStatus) {
            const action = newStatus === '1' ? 'ENABLE' : 'DISABLE';
            const status = newStatus === '1' ? 'Available' : 'Unavailable';
            return confirm(`${action} "${labName}" for ${date}?\n\nThis affects ONLY this specific date.\nOther dates remain unchanged.\n\nContinue?`);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.toggle-button');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (this.getAttribute('onclick')) {
                        const confirmMsg = this.getAttribute('onclick');
                        e.preventDefault();
                        const result = eval(confirmMsg.replace('return ', ''));
                        if (result) {
                            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                            this.style.pointerEvents = 'none';
                            window.location.href = this.href;
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
