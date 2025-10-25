<?php
// login.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter both username and password']);
        exit;
    }
    
    // First, check in admin table
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        // Check admin password (not hashed as per requirement)
        if ($password === $admin['password']) {
            $_SESSION['user_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['name'] = $admin['name'];
            $_SESSION['role'] = 'admin';
            $_SESSION['email'] = $admin['email'];
            $_SESSION['logged_in'] = true;
            
            echo json_encode(['success' => true, 'role' => 'admin']);
            exit;
        }
    }
    
    // If not admin, check in user table (officers)
    $stmt = $pdo->prepare("SELECT * FROM user WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Check officer password - try both Password and password2 fields
        if ($password === $user['Password'] || $password === $user['password2']) {
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['name'] = $user['Name'];
            $_SESSION['role'] = $user['role'] ?? 'officer'; // Use 'officer' if role is null
            $_SESSION['email'] = $user['email'];
            $_SESSION['dsid'] = $user['dsid'];
            $_SESSION['district_id'] = $user['district_id'];
            $_SESSION['province_id'] = $user['Province_id'];
            $_SESSION['position'] = $user['Position'];
            $_SESSION['contact'] = $user['Contact'];
            $_SESSION['logged_in'] = true;
            
            echo json_encode(['success' => true, 'role' => $_SESSION['role']]);
            exit;
        }
    }
    
    // If we reach here, login failed
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}
?>