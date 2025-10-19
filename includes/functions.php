<?php
if (!defined('APP_ACCESS')) die('Direct access not permitted');

// Start Secure Session
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_samesite', 'Strict');
        session_start();
    }
}

// CSRF Token Functions
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// XSS Protection - Escape Output
function e($data) {
    if (is_array($data)) {
        return array_map('e', $data);
    }
    // Handle null values for PHP 8.1+ compatibility
    if ($data === null) {
        return '';
    }
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

// Clean Input
function clean($data) {
    if ($data === null) {
        return '';
    }
    return trim(stripslashes($data));
}

// Redirect Function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Flash Message Functions
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Validation Functions
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidNIK($nik) {
    return preg_match('/^\d{16}$/', $nik);
}

function isValidPhone($phone) {
    return preg_match('/^[0-9]{10,13}$/', $phone);
}

// Password Functions
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// URL Helper
function base_url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}