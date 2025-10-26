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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_ability'])) {
        // Add new ability
        $ability_name = trim($_POST['ability_name']);
        $where_used = trim($_POST['where_used']);
        
        if (!empty($ability_name)) {
            $stmt = $pdo->prepare("INSERT INTO ability (user_id, ability_name, where_used) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $ability_name, $where_used]);
            $success_message = "Ability added successfully!";
        }
    } elseif (isset($_POST['update_ability'])) {
        // Update ability
        $ability_id = $_POST['ability_id'];
        $ability_name = trim($_POST['ability_name']);
        $where_used = trim($_POST['where_used']);
        
        if (!empty($ability_name)) {
            $stmt = $pdo->prepare("UPDATE ability SET ability_name = ?, where_used = ?, updated_at = CURRENT_TIMESTAMP WHERE ability_id = ? AND user_id = ?");
            $stmt->execute([$ability_name, $where_used, $ability_id, $_SESSION['user_id']]);
            $success_message = "Ability updated successfully!";
        }
    } elseif (isset($_POST['delete_ability'])) {
        // Delete ability
        $ability_id = $_POST['ability_id'];
        $stmt = $pdo->prepare("DELETE FROM ability WHERE ability_id = ? AND user_id = ?");
        $stmt->execute([$ability_id, $_SESSION['user_id']]);
        $success_message = "Ability deleted successfully!";
    }
}

// Get all abilities for this officer
$stmt = $pdo->prepare("SELECT * FROM ability WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$abilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if we're editing or deleting an ability
$editing_ability = null;
$deleting_ability = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM ability WHERE ability_id = ? AND user_id = ?");
    $stmt->execute([$_GET['edit'], $_SESSION['user_id']]);
    $editing_ability = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT * FROM ability WHERE ability_id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
    $deleting_ability = $stmt->fetch(PDO::FETCH_ASSOC);
}
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
            font-size: 28px;
        }

        .profile-info p {
            color: #666;
            font-size: 18px;
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
            font-size: 14px;
        }

        .detail-item span {
            color: #666;
            font-size: 16px;
        }

        /* Form Container Styles */
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-container h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            background: #5a6268;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            background: #c82333;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* Delete Form Specific Styles */
        .delete-form {
            background: #fff5f5;
            border: 2px solid #fed7d7;
        }

        .delete-form h3 {
            color: #c53030;
            border-bottom-color: #fed7d7;
        }

        .delete-warning {
            background: #fed7d7;
            color: #c53030;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #f56565;
        }

        .delete-warning strong {
            display: block;
            margin-bottom: 5px;
            font-size: 16px;
        }

        /* Edit Form Specific Styles */
        .edit-form {
            background: #f0fff4;
            border: 2px solid #c6f6d5;
        }

        .edit-form h3 {
            color: #2f855a;
            border-bottom-color: #c6f6d5;
        }

        /* Abilities List Styles */
        .abilities-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .abilities-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
        }

        .abilities-grid {
            display: grid;
            gap: 15px;
        }

        .ability-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .ability-info h4 {
            color: #333;
            margin-bottom: 8px;
            font-size: 18px;
        }

        .ability-info p {
            color: #666;
            margin-bottom: 5px;
        }

        .ability-date {
            color: #999;
            font-size: 12px;
        }

        .ability-actions {
            display: flex;
            gap: 10px;
        }

        .btn-edit {
            background: #ffc107;
            color: #212529;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .no-abilities {
            text-align: center;
            color: #666;
            padding: 40px;
            font-style: italic;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
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
            
            .profile-info h2 {
                font-size: 24px;
            }
            
            .profile-info p {
                font-size: 16px;
            }
            
            .ability-item {
                flex-direction: column;
                gap: 15px;
            }
            
            .ability-actions {
                align-self: flex-end;
            }
            
            .form-actions {
                flex-direction: column;
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
        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

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

        <!-- Delete Ability Form -->
        <?php if ($deleting_ability): ?>
            <div class="form-container delete-form">
                <h3>🗑️ Delete Ability</h3>
                <div class="delete-warning">
                    <strong>Warning: This action cannot be undone!</strong>
                    You are about to permanently delete this ability from your profile.
                </div>
                <form method="POST">
                    <input type="hidden" name="ability_id" value="<?php echo $deleting_ability['ability_id']; ?>">
                    
                    <div class="form-group">
                        <label>Ability Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($deleting_ability['ability_name']); ?>" readonly style="background: #f7f7f7; cursor: not-allowed;">
                    </div>
                    
                    <?php if (!empty($deleting_ability['where_used'])): ?>
                    <div class="form-group">
                        <label>Where Used</label>
                        <textarea readonly style="background: #f7f7f7; cursor: not-allowed;"><?php echo htmlspecialchars($deleting_ability['where_used']); ?></textarea>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-actions">
                        <button type="submit" name="delete_ability" class="btn-danger">Yes, Delete Permanently</button>
                        <a href="officer_dashboard.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Edit Ability Form -->
        <?php if ($editing_ability): ?>
            <div class="form-container edit-form">
                <h3>✏️ Edit Ability</h3>
                <form method="POST">
                    <input type="hidden" name="ability_id" value="<?php echo $editing_ability['ability_id']; ?>">
                    
                    <div class="form-group">
                        <label for="ability_name">Ability Name *</label>
                        <input type="text" id="ability_name" name="ability_name" 
                               value="<?php echo htmlspecialchars($editing_ability['ability_name']); ?>" 
                               required placeholder="e.g., Singing, Public Speaking, Event Decoration">
                    </div>
                    
                    <div class="form-group">
                        <label for="where_used">Where You Used This Ability (Optional)</label>
                        <textarea id="where_used" name="where_used" 
                                  placeholder="e.g., Used in office events, community programs, etc."><?php echo htmlspecialchars($editing_ability['where_used']); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_ability" class="btn-primary">Update Ability</button>
                        <a href="officer_dashboard.php?delete=<?php echo $editing_ability['ability_id']; ?>" class="btn-danger">Delete This Ability</a>
                        <a href="officer_dashboard.php" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Add New Ability Form -->
        <?php if (!$editing_ability && !$deleting_ability): ?>
            <div class="form-container">
                <h3>➕ Add New Ability</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="ability_name">Ability Name *</label>
                        <input type="text" id="ability_name" name="ability_name" 
                               required placeholder="e.g., Singing, Public Speaking, Event Decoration">
                    </div>
                    
                    <div class="form-group">
                        <label for="where_used">Where You Used This Ability (Optional)</label>
                        <textarea id="where_used" name="where_used" 
                                  placeholder="e.g., Used in office events, community programs, etc."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="add_ability" class="btn-primary">Save Ability</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Abilities List -->
        <div class="abilities-section">
            <h3>My Abilities (<?php echo count($abilities); ?>)</h3>
            
            <?php if (empty($abilities)): ?>
                <div class="no-abilities">
                    No abilities added yet. Start by adding your first ability above!
                </div>
            <?php else: ?>
                <div class="abilities-grid">
                    <?php foreach ($abilities as $ability): ?>
                        <div class="ability-item">
                            <div class="ability-info">
                                <h4><?php echo htmlspecialchars($ability['ability_name']); ?></h4>
                                <?php if (!empty($ability['where_used'])): ?>
                                    <p><strong>Where Used:</strong> <?php echo htmlspecialchars($ability['where_used']); ?></p>
                                <?php endif; ?>
                                <p class="ability-date">
                                    Added: <?php echo date('M j, Y g:i A', strtotime($ability['created_at'])); ?>
                                    <?php if ($ability['updated_at'] != $ability['created_at']): ?>
                                        | Updated: <?php echo date('M j, Y g:i A', strtotime($ability['updated_at'])); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="ability-actions">
                                <a href="officer_dashboard.php?edit=<?php echo $ability['ability_id']; ?>" class="btn-edit" title="Edit Ability">✏️</a>
                                <a href="officer_dashboard.php?delete=<?php echo $ability['ability_id']; ?>" class="btn-delete" title="Delete Ability">🗑️</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
