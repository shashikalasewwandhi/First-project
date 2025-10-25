<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit;
}

// Get statistics for admin dashboard
$total_officers = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
$total_divisions = $pdo->query("SELECT COUNT(*) FROM dsdivision")->fetchColumn();
$total_districts = $pdo->query("SELECT COUNT(*) FROM district")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Divisional Office</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f5f5f5;
        }

        .header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .welcome p {
            opacity: 0.9;
            font-size: 14px;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 4px solid #ff6b6b;
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .admin-actions {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .admin-actions h3 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            background: white;
            border: 2px solid #e0e0e0;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
        }

        .action-btn:hover {
            border-color: #ff6b6b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.1);
        }

        .action-btn i {
            font-size: 28px;
            margin-bottom: 10px;
            display: block;
            color: #ff6b6b;
        }

        .action-btn span {
            display: block;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="welcome">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
                <p>Administrator Dashboard - Divisional Office System</p>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="dashboard-container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_officers; ?></div>
                <div class="stat-label">Total Officers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_divisions; ?></div>
                <div class="stat-label">Divisional Secretariats</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_districts; ?></div>
                <div class="stat-label">Districts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">9</div>
                <div class="stat-label">Provinces</div>
            </div>
        </div>

        <div class="admin-actions">
            <h3>Administrative Actions</h3>
            <div class="action-buttons">
                <a href="manage_officers.php" class="action-btn">
                    <i>👥</i>
                    <span>Manage Officers</span>
                </a>
                <a href="view_abilities.php" class="action-btn">
                    <i>🔍</i>
                    <span>Search Abilities</span>
                </a>
                <a href="view_reports.php" class="action-btn">
                    <i>📊</i>
                    <span>View Reports</span>
                </a>
                <a href="system_settings.php" class="action-btn">
                    <i>⚙️</i>
                    <span>System Settings</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>