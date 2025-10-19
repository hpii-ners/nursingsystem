<?php
if (!defined('APP_ACCESS')) die('Direct access not permitted');

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Require Login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(base_url('login.php'));
    }
}

// Get Current User
function currentUser() {
    if (!isLoggedIn()) return null;
    
    static $user = null;
    
    if ($user === null) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, username, nama_lengkap, email, nik, created_at, updated_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
    
    return $user;
}

// Login User
function loginUser($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, password, nama_lengkap, email, nik FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && verifyPassword($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_name'] = $user['nama_lengkap'];
        return true;
    }
    
    return false;
}

// Logout User
function logoutUser() {
    session_destroy();
}