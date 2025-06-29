<?php
session_start();

if ($_SESSION['role'] !== 'Lab_TO') {
    header("Location: ../login/login_form.html");
    exit;
}

include '../db_connect.php';

// Get selected date (default to today)
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Check if Equipment_Availability table exists, create if not
$table_check = $conn->query("SHOW TABLES LIKE 'Equipment_Availability'");
if ($table_check->num_rows == 0) {
    $create_table = "CREATE TABLE Equipment_Availability (
        id INT AUTO_INCREMENT PRIMARY KEY,
        Equipment_ID INT NOT NULL,
        availability_date DATE NOT NULL,
        is_available BOOLEAN DEFAULT TRUE,
        notes TEXT,
        updated_by INT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_equipment_date (Equipment_ID, availability_date)
    )";
    $conn->query($create_table);
}

// Query to get equipment with date-specific availability
$sql = "SELECT e.Equipment_ID, e.Equipment_Name, e.Capacity, e.Availability as general_availability,
               COALESCE(ea.is_available, e.Availability) as date_availability,
               ea.notes, ea.updated_at,
               CASE WHEN ea.is_available IS NOT NULL THEN 1 ELSE 0 END as has_date_override
        FROM Lab_Equipment e
        LEFT JOIN Equipment_Availability ea ON e.Equipment_ID = ea.Equipment_ID AND ea.availability_date = ?
        ORDER BY e.Equipment_ID";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();

// Calculate statistics for selected date
$total_equipment = 0;
$available_equipment = 0;
$unavailable_equipment = 0;

$equipment_list = [];
while ($row = $result->fetch_assoc()) {
    $equipment_list[] = $row;
    $total_equipment++;
    if ($row['date_availability']) {
        $available_equipment++;
    } else {
        $unavailable_equipment++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Date-Specific Equipment Availability Management</title>
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
        .success-banner { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; padding: 16px; border-radius: 12px; margin-bottom: 24px; text-align: center; border-left: 4px solid #10b981; font-size: 0.9rem; }
        .info-banner { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e40af; padding: 16px; border-radius: 12px; margin-bottom: 24px; text-align: center; border-left: 4px solid #3b82f6; font-size: 0.9rem; }
        .stats-overview { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: linear-gradient(135deg, #f8fafc, #e2e8f0); padding: 24px 20px; border-radius: 16px; text-align: center; transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1); }
        .stat-icon { font-size: 2.5rem; margin-bottom: 16px; background: linear-gradient(135deg, #4f46e5, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-number { font-size: 2rem; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .stat-label { color: #64748b; font-weight: 600; font-size: 0.9rem; }
        .equipment-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 32px; }
        .equipment-card { background: white; border-radius: 16px; box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08); overflow: hidden; transition: all 0.3s ease; border: 1px solid #e2e8f0; }
        .equipment-card:hover { transform: translateY(-4px); box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12); }
        .equipment-card-header { background: linear-gradient(135deg, #f8fafc, #e2e8f0); padding: 20px; border-bottom: 1px solid #e2e8f0; }
        .equipment-title { font-size: 1.2rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
        .equipment-id { background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 3px 10px; border-radius: 10px; font-size: 0.75rem; font-weight: 600; }
        .override-indicator { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 3px 6px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; }
        .equipment-body { padding: 20px; }
        .equipment-info { display: grid; grid-template-columns: 1fr; gap: 12px; margin-bottom: 20px; }
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
        .empty-state { text-align: center; padding: 60px 20px; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 15px; }
        .empty-state i { font-size: 4rem; color: #cbd5e1; margin-bottom: 20px; }
        @media (max-width: 768px) { .equipment-grid { grid-template-columns: 1fr; } .date-input-wrapper { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <h1><i class="fas fa-tools"></i> Date-Specific Equipment Availability</h1>
            <p>Manage equipment availability for individual dates</p>
        </div>

        <div class="date-picker-section">
            <div class="date-picker-container">
                <h2 class="date-picker-title">
                    <i class="fas fa-calendar-alt"></i> Select Date
                </h2>
                <p class="date-picker-subtitle">Choose any date to manage equipment availability for that specific day</p>
                
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
                    <strong>Success!</strong> Equipment "<?= htmlspecialchars($_GET['equipment'] ?? 'Unknown') ?>" availability updated for <?= date('F j, Y', strtotime($selected_date)) ?> only.
                </div>
            <?php endif; ?>

            <div class="info-banner">
                <i class="fas fa-info-circle"></i>
                <strong>Date-Independent:</strong> Changes affect ONLY <?= date('M j, Y', strtotime($selected_date)) ?>. Other dates remain unchanged.
            </div>

            <div class="stats-overview">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-tools"></i></div>
                    <div class="stat-number"><?= $total_equipment ?></div>
                    <div class="stat-label">Total Equipment</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number"><?= $available_equipment ?></div>
                    <div class="stat-label">Available</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-number"><?= $unavailable_equipment ?></div>
                    <div class="stat-label">Unavailable</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-percentage"></i></div>
                    <div class="stat-number"><?= $total_equipment > 0 ? round(($available_equipment / $total_equipment) * 100) : 0 ?>%</div>
                    <div class="stat-label">Availability Rate</div>
                </div>
            </div>

            <?php if (count($equipment_list) > 0): ?>
                <div class="equipment-grid">
                    <?php foreach ($equipment_list as $row): ?>
                        <?php
                        $equipmentID = htmlspecialchars($row['Equipment_ID']);
                        $equipmentName = htmlspecialchars($row['Equipment_Name']);
                        $capacity = htmlspecialchars($row['Capacity']);
                        $dateAvailability = $row['date_availability'];
                        $hasOverride = $row['has_date_override'];
                        $toggleValue = $dateAvailability ? 0 : 1;
                        
                        // Determine icon based on equipment name
                        $icon = 'fas fa-cog';
                        if (stripos($equipmentName, 'microscope') !== false) $icon = 'fas fa-microscope';
                        elseif (stripos($equipmentName, 'computer') !== false) $icon = 'fas fa-desktop';
                        elseif (stripos($equipmentName, 'printer') !== false) $icon = 'fas fa-print';
                        elseif (stripos($equipmentName, 'projector') !== false) $icon = 'fas fa-video';
                        elseif (stripos($equipmentName, 'camera') !== false) $icon = 'fas fa-camera';
                        elseif (stripos($equipmentName, 'scanner') !== false) $icon = 'fas fa-scanner';
                        ?>
                        <div class="equipment-card">
                            <div class="equipment-card-header">
                                <div class="equipment-title">
                                    <i class="<?= $icon ?>"></i>
                                    <?= $equipmentName ?>
                                    <span class="equipment-id">ID: <?= $equipmentID ?></span>
                                    <?php if ($hasOverride): ?>
                                        <span class="override-indicator">CUSTOM</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="equipment-body">
                                <div class="equipment-info">
                                    <div class="info-item">
                                        <div class="info-label">Capacity</div>
                                        <div class="info-value">
                                            <i class="fas fa-layer-group"></i> <?= $capacity ?> Units
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

                                <a href="update_equipment.php?equipment_id=<?= $equipmentID ?>&date=<?= $selected_date ?>&status=<?= $toggleValue ?>"
                                   class="toggle-button <?= $dateAvailability ? 'toggle-disable' : 'toggle-enable' ?>"
                                   onclick="return confirmDateToggle('<?= $equipmentName ?>', '<?= date('F j, Y', strtotime($selected_date)) ?>', '<?= $toggleValue ?>');">
                                    <i class="fas <?= $dateAvailability ? 'fa-ban' : 'fa-check' ?>"></i>
                                    <?= $dateAvailability ? 'Make Unavailable' : 'Make Available' ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-tools"></i>
                    <h3>No Equipment Found</h3>
                    <p>No equipment data found. Please add equipment to the system.</p>
                </div>
            <?php endif; ?>

            <div style="text-align: center; padding-top: 32px; border-top: 1px solid #e2e8f0;">
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="view_labs.php" class="btn btn-secondary">
                    <i class="fas fa-flask"></i> View Labs
                </a>
            </div>
        </div>
    </div>

    <script>
        function confirmDateToggle(equipmentName, date, newStatus) {
            const action = newStatus === '1' ? 'ENABLE' : 'DISABLE';
            const status = newStatus === '1' ? 'Available' : 'Unavailable';
            return confirm(`${action} "${equipmentName}" for ${date}?\n\nThis affects ONLY this specific date.\nContinue?`);
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

    <?php $conn->close(); ?>
</body>
</html>
