<?php
define('APP_ACCESS', true);
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

startSession();
logoutUser();
redirect('login.php');