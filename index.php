<?php
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user'])) {
    header('Location: public/index.php');
    exit();
}

// Jika belum login, redirect ke login page
header('Location: public/login.php');
exit();
?>