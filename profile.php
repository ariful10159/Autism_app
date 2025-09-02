<?php
require_once __DIR__ . '/config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$pdo = get_db();
$user = current_user();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $age = isset($_POST['age']) ? intval($_POST['age']) : null;
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $jaundice = isset($_POST['jaundice']) ? trim($_POST['jaundice']) : '';
    $family_history = isset($_POST['family_history']) ? trim($_POST['family_history']) : '';
    // Handle optional profile photo upload
    $photoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $mime = mime_content_type($_FILES['photo']['tmp_name']);
        if (!in_array($mime, $allowedTypes)) {
            $error = 'Unsupported profile photo type. Please upload JPG or PNG.';
        } else {
            // Create profile uploads directory
            $profileDir = __DIR__ . '/uploads/profiles';
            if (!file_exists($profileDir)) {
                mkdir($profileDir, 0777, true);
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user['id'] . '_' . time() . '.' . $ext;
            $destination = $profileDir . '/' . $filename;
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                $error = 'Failed to upload profile photo.';
            } else {
                $photoPath = 'uploads/profiles/' . $filename;
                // Optionally delete old photo
                if ($user['profile_photo']) {
                    $oldPath = __DIR__ . '/' . $user['profile_photo'];
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }
        }
    }
    if (!$error) {
        if (!$name || !$email) {
            $error = 'Name and email cannot be empty.';
        } elseif ($age === null || $age < 0) {
            $error = 'Please enter a valid age.';
        } elseif (!in_array($gender, ['male', 'female', 'others'])) {
            $error = 'Please select a valid gender.';
        } elseif (!in_array($jaundice, ['yes', 'no'])) {
            $error = 'Please select if you had jaundice (yes or no).';
        } elseif (!in_array($family_history, ['yes', 'no'])) {
            $error = 'Please select if you have a family history of ASD (yes or no).';
        } else {
            // Check if email exists for another user
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id');
            $stmt->execute([':email' => $email, ':id' => $user['id']]);
            if ($stmt->fetch()) {
                $error = 'This email is already taken.';
            } else {
                // Build update query
                if ($photoPath) {
                    $stmt = $pdo->prepare('UPDATE users SET name = :name, email = :email, profile_photo = :photo, age = :age, gender = :gender, jaundice = :jaundice, family_history = :family_history WHERE id = :id');
                    $stmt->execute([
                        ':name' => $name,
                        ':email' => $email,
                        ':photo' => $photoPath,
                        ':age' => $age,
                        ':gender' => $gender,
                        ':jaundice' => $jaundice,
                        ':family_history' => $family_history,
                        ':id' => $user['id']
                    ]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET name = :name, email = :email, age = :age, gender = :gender, jaundice = :jaundice, family_history = :family_history WHERE id = :id');
                    $stmt->execute([
                        ':name' => $name,
                        ':email' => $email,
                        ':age' => $age,
                        ':gender' => $gender,
                        ':jaundice' => $jaundice,
                        ':family_history' => $family_history,
                        ':id' => $user['id']
                    ]);
                }
                $success = 'Profile updated successfully.';
                // Refresh user data
                $user = current_user();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - ASD Detection App</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <!-- Begin responsive header and navigation -->
    <input type="checkbox" id="menu-toggle" />
    <div class="mobile-header">
        <div class="logo-name">
            <img src="assets/autism_art.png" alt="ASD art">
            <span>Your Profile</span>
        </div>
        <label for="menu-toggle" class="burger" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </label>
    </div>
    <nav class="mobile-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="detection.php">Face Detection</a>
        <a href="questionnaire.php">Questionnaire</a>
        <a href="suggestions.php">Suggestions</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" style="color:#dc3545;">Logout</a>
    </nav>
    <main>
        <div class="container">
            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>
            <!-- Profile Picture and Name -->
            <div style="text-align:center; margin-bottom:1rem;">
                <?php if (!empty($user['profile_photo'])): ?>
                    <img src="<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile Photo" style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:2px solid #007bff;">
                <?php else: ?>
                    <div style="width:100px;height:100px;border-radius:50%;background-color:#dee2e6;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;color:#6c757d;">👤</div>
                <?php endif; ?>
                <h3><?= htmlspecialchars($user['name']) ?></h3>
            </div>
            <form method="post" action="profile.php" enctype="multipart/form-data">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                <label for="age">Age</label>
                <input type="number" name="age" id="age" min="0" value="<?= htmlspecialchars($user['age'] ?? '') ?>" required>
                <label for="gender">Gender</label>
                <select name="gender" id="gender" required>
                    <option value="" disabled <?= empty($user['gender']) ? 'selected' : '' ?>>Select gender</option>
                    <option value="male" <?= (isset($user['gender']) && $user['gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= (isset($user['gender']) && $user['gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                    <option value="others" <?= (isset($user['gender']) && $user['gender'] === 'others') ? 'selected' : '' ?>>Others</option>
                </select>
                <label for="jaundice">Jaundice (as a baby)</label>
                <select name="jaundice" id="jaundice" required>
                    <option value="" disabled <?= empty($user['jaundice']) ? 'selected' : '' ?>>Select</option>
                    <option value="yes" <?= (isset($user['jaundice']) && $user['jaundice'] === 'yes') ? 'selected' : '' ?>>Yes</option>
                    <option value="no" <?= (isset($user['jaundice']) && $user['jaundice'] === 'no') ? 'selected' : '' ?>>No</option>
                </select>
                <label for="family_history">Family history of ASD</label>
                <select name="family_history" id="family_history" required>
                    <option value="" disabled <?= empty($user['family_history']) ? 'selected' : '' ?>>Select</option>
                    <option value="yes" <?= (isset($user['family_history']) && $user['family_history'] === 'yes') ? 'selected' : '' ?>>Yes</option>
                    <option value="no" <?= (isset($user['family_history']) && $user['family_history'] === 'no') ? 'selected' : '' ?>>No</option>
                </select>
                <label for="photo">Profile Photo (optional)</label>
                <input type="file" name="photo" id="photo" accept="image/*">
                <input type="submit" value="">
            </form>
        </div>
    </main>
</body>
</html>