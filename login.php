<?php
require_once __DIR__ . '/config.php';
session_start();
// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
// Initialize error message
$error = '';
// Process login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } else if (login_user($email, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ASD Detection App</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <!-- Unified mobile header for login page -->
    <div class="mobile-header">
        <div class="logo-name">
            <img src="assets/autism_art.png" alt="ASD art">
            <span>ASD Detection App</span>
        </div>
    </div>
    <main>
        <div class="container">
            <h2>Login</h2>
            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="post" action="login.php">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                <input type="submit" value="Login">
            </form>
            <div class="link">
                <a href="signup.php">Don't have an account? Sign up</a>
            </div>
        </div>
    </main>
</body>
</html>