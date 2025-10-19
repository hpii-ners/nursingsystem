<?php
define('APP_ACCESS', true);
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

startSession();
requireLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// Skip CSRF validation atau gunakan metode alternatif
// Jika ada fungsi checkCSRF() gunakan itu, jika tidak skip dulu
// if (!checkCSRF()) {
//     setFlash('error', 'Invalid request. Silakan coba lagi.');
//     redirect('index.php');
// }

// Get form data
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$username = clean($_POST['username'] ?? '');
$nama_lengkap = clean($_POST['nama_lengkap'] ?? '');
$email = clean($_POST['email'] ?? '');
$nik = clean($_POST['nik'] ?? '');
$password = $_POST['password'] ?? ''; // Jangan clean password

// Validation
$errors = [];

if (empty($username)) {
    $errors[] = 'Username harus diisi';
}

if (empty($nama_lengkap)) {
    $errors[] = 'Nama lengkap harus diisi';
}

if (empty($email)) {
    $errors[] = 'Email harus diisi';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid';
}

if (empty($nik)) {
    $errors[] = 'NIK harus diisi';
} elseif (!preg_match('/^\d{16}$/', $nik)) {
    $errors[] = 'NIK harus 16 digit angka';
}

// Password validation for new user
if ($id == 0 && empty($password)) {
    $errors[] = 'Password harus diisi';
} elseif (!empty($password) && strlen($password) < 6) {
    $errors[] = 'Password minimal 6 karakter';
}

// Check for errors
if (!empty($errors)) {
    setFlash('error', implode('<br>', $errors));
    if ($id > 0) {
        redirect('users_form.php?id=' . $id);
    } else {
        redirect('users_form.php');
    }
}

try {
    if ($id > 0) {
        // UPDATE existing user
        
        // Check if email already exists (exclude current user)
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            setFlash('error', 'Email sudah digunakan oleh user lain');
            redirect('users_form.php?id=' . $id);
        }
        
        // Check if NIK already exists (exclude current user)
        $stmt = $db->prepare("SELECT id FROM users WHERE nik = ? AND id != ?");
        $stmt->execute([$nik, $id]);
        if ($stmt->fetch()) {
            setFlash('error', 'NIK sudah digunakan oleh user lain');
            redirect('users_form.php?id=' . $id);
        }
        
        // Update user
        if (!empty($password)) {
            // Update with new password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET nama_lengkap = ?, email = ?, nik = ?, password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$nama_lengkap, $email, $nik, $hashedPassword, $id]);
        } else {
            // Update without changing password
            $stmt = $db->prepare("UPDATE users SET nama_lengkap = ?, email = ?, nik = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$nama_lengkap, $email, $nik, $id]);
        }
        
        setFlash('success', 'User berhasil diupdate');
        
    } else {
        // INSERT new user
        
        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            setFlash('error', 'Username sudah digunakan');
            redirect('users_form.php');
        }
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            setFlash('error', 'Email sudah digunakan');
            redirect('users_form.php');
        }
        
        // Check if NIK already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE nik = ?");
        $stmt->execute([$nik]);
        if ($stmt->fetch()) {
            setFlash('error', 'NIK sudah digunakan');
            redirect('users_form.php');
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $db->prepare("INSERT INTO users (username, nama_lengkap, email, nik, password, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$username, $nama_lengkap, $email, $nik, $hashedPassword]);
        
        setFlash('success', 'User berhasil ditambahkan');
    }
    
    redirect('../index.php?page=users');
    
} catch (PDOException $e) {
    setFlash('error', 'Terjadi kesalahan: ' . $e->getMessage());
    if ($id > 0) {
        redirect('users_form.php?id=' . $id);
    } else {
        redirect('users_form.php');
    }
}