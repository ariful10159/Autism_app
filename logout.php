<?php
require_once __DIR__ . '/config.php';
session_start();
logout_user();
header('Location: login.php');
exit;