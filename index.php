<?php
require_once __DIR__ . '/config.php';
// Start session and check user
session_start();
if (isset($_SESSION['user_id'])) {
    // Redirect to dashboard
    header('Location: dashboard.php');
    exit;
}
// If not logged in, redirect to login page
header('Location: login.php');
exit;