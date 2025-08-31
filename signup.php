<?php
require_once __DIR__ . '/config.php';
session_start();
// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');
    if (!$name || !$email || !$password) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm) {
        $error = 'Password and confirmation do not match.';
    } else {
        $result = register_user($name, $email, $password);
        if ($result === true) {
            $success = 'Registration successful! You can now log in.';
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ASD Detection App</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <!-- Unified mobile header for signup page -->
    <div class="mobile-header">
        <div class="logo-name">
            <img src="assets/autism_art.png" alt="ASD art">
            <span>ASD Detection App</span>
        </div>
    </div>
    <main>
        <div class="container">
            <h2>Sign Up</h2>
            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>
            <form method="post" action="signup.php">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" required>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                <label for="confirm">Confirm Password</label>
                <input type="password" name="confirm" id="confirm" required>
                <input type="submit" value="Sign Up">
            </form>
            <div class="link">
                <a href="login.php">Already have an account? Log in</a>
            </div>
        </div>
    </main>
</body>
</html>