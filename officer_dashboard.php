<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is an officer
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] === 'admin') {
    header('Location: login.html');
    exit;
}

// Get officer details from database
$stmt = $pdo->prepare("SELECT u.*, d.dsname, dist.district_name, p.province_name 
                       FROM user u 
                       LEFT JOIN dsdivision d ON u.dsid = d.dsid 
                       LEFT JOIN district dist ON u.district_id = dist.district_id 
                       LEFT JOIN provinces p ON u.Province_id = p.province_id 
                       WHERE u.ID = ?");
$stmt->execute([$_SESSION['user_id']]);
$officer = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Dashboard - Divisional Office</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .profile-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            margin-right: 20px;
        }

        .profile-info h2 {
            color: #333;
            margin-bottom: 5px;
        }

        .profile-info p {
            color: #666;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .detail-item label {
            font-weight: 600;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .detail-item span {
            color: #666;
        }

        .actions-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .actions-section h3 {
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
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
        }

        .action-btn:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
        }

        .action-btn i {
            font-size: 24px;
            margin-bottom: 10px;
            display: block;
            color: #667eea;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-icon {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="welcome">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
                <p>Officer Dashboard - Divisional Office System</p>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="dashboard-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-icon">
                    👤
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($officer['Name']); ?></h2>
                    <p><?php echo htmlspecialchars($officer['Position'] ?? 'Officer'); ?></p>
                </div>
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <label>Employee ID</label>
                    <span><?php echo htmlspecialchars($officer['ID']); ?></span>
                </div>
                <div class="detail-item">
                    <label>NIC Number</label>
                    <span><?php echo htmlspecialchars($officer['nic']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Email</label>
                    <span><?php echo htmlspecialchars($officer['email']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Contact</label>
                    <span><?php echo htmlspecialchars($officer['Contact'] ?? 'Not provided'); ?></span>
                </div>
                <div class="detail-item">
                    <label>Division</label>
                    <span><?php echo htmlspecialchars($officer['dsname'] ?? 'Not assigned'); ?></span>
                </div>
                <div class="detail-item">
                    <label>District</label>
                    <span><?php echo htmlspecialchars($officer['district_name'] ?? 'Not assigned'); ?></span>
                </div>
                <div class="detail-item">
                    <label>Province</label>
                    <span><?php echo htmlspecialchars($officer['province_name'] ?? 'Not assigned'); ?></span>
                </div>
                <div class="detail-item">
                    <label>Role</label>
                    <span><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></span>
                </div>
            </div>
        </div>

        <div class="actions-section">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="add_ability.php" class="action-btn">
                    <i>➕</i>
                    Add Ability
                </a>
                <a href="view_abilities.php" class="action-btn">
                    <i>👁️</i>
                    View My Abilities
                </a>
                <a href="edit_profile.php" class="action-btn">
                    <i>⚙️</i>
                    Edit Profile
                </a>
                <a href="view_profile.php" class="action-btn">
                    <i>📄</i>
                    View Profile
                </a>
            </div>
        </div>
    </div>
</body>
</html>