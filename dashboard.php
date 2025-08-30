<?php
require_once __DIR__ . '/config.php';
session_start();
// If not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Retrieve current user; if retrieval fails, log out and redirect
$user = current_user();
if (!$user) {
    // Session may be invalid; log out and redirect
    logout_user();
    header('Location: login.php');
    exit;
}

// Fetch the latest detection and questionnaire records for this user
$userId = $_SESSION['user_id'];
$latestDetection = get_latest_detection($userId);
$latestQuestionnaire = get_latest_questionnaire($userId);

// Determine if a photo has been uploaded
$photoUploaded = ($latestDetection !== null);

// Initialize evaluation variables
$classification = null;
$note = '';
$badgeClass = '';
$score = null;
$totalQuestions = 0;

if ($latestQuestionnaire) {
    $answers = json_decode($latestQuestionnaire['answers'], true);
    $score = $latestQuestionnaire['score'];
    $totalQuestions = is_array($answers) ? count($answers) : 0;
}

// Compute classification only if both detection and questionnaire exist
if ($photoUploaded && $latestQuestionnaire) {
    // Define thresholds: score >= 6 → ASD, score >= 4 → Monitor, else No ASD
    if ($score !== null && $score >= 6) {
        $classification = 'ASD';
        $badgeClass = 'badge-asd';
        $note = 'Elevated indicators detected. Consider consulting a professional.';
    } elseif ($score !== null && $score >= 4) {
        $classification = 'Monitor';
        $badgeClass = 'badge-monitor';
        $note = 'Some indicators present. Monitor and re‑check soon.';
    } else {
        $classification = 'No ASD';
        $badgeClass = 'badge-noas';
        $note = 'No immediate concerns from current inputs.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ASD Detection App</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <!-- Responsive header with hamburger menu -->
    <input type="checkbox" id="menu-toggle" />
    <div class="mobile-header">
        <div class="logo-name">
            <img src="assets/autism_art.png" alt="ASD art">
            <span>Welcome, <?= htmlspecialchars($user['name']) ?></span>
        </div>
        <label for="menu-toggle" class="burger" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </label>
    </div>
    <!-- Slide‑down navigation links -->
    <nav class="mobile-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="detection.php">Face Detection</a>
        <a href="questionnaire.php">Questionnaire</a>
        <a href="suggestions.php">Suggestions</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" style="color:#dc3545;">Logout</a>
    </nav>
    <main>
        <!-- Show evaluation card if both photo and questionnaire exist -->
<?php if ($classification): ?>
        <div class="evaluation-card">
            <h2>Evaluation <span class="badge <?= $badgeClass ?>"><?= $classification ?></span></h2>
            <p class="evaluation-note"><?= htmlspecialchars($note) ?></p>
            <div class="stat-group">
                <div class="stat-chip">
                    <span class="stat-label">Photo</span>
                    <span class="stat-value"><?= $photoUploaded ? 'Uploaded' : 'Missing' ?></span>
                </div>
                <div class="stat-chip">
                    <span class="stat-label">AQ Score</span>
                    <span class="stat-value"><?= ($score !== null) ? htmlspecialchars($score) . ' / ' . $totalQuestions : 'N/A' ?></span>
                </div>
            </div>
            <p class="disclaimer">This tool provides screening support only and is <strong>not a diagnosis</strong>.</p>
        </div>
<?php else: ?>
        <!-- If no complete data, guide the user -->
        <div class="container">
            <p>You must complete both the photo upload and the questionnaire to view your evaluation.</p>
            <?php if (!$photoUploaded): ?>
                <p>Please visit the <a href="detection.php">Face Detection</a> page to upload your photo.</p>
            <?php endif; ?>
            <?php if (!$latestQuestionnaire): ?>
                <p>Please visit the <a href="questionnaire.php">Questionnaire</a> page to answer the questions.</p>
            <?php endif; ?>
        </div>
<?php endif; ?>

        <!-- Main content/instructions -->
        <div class="container">
            <p>Select an option above to begin. You can take a photo for ASD detection, fill out a questionnaire, view personalized suggestions, or edit your profile.</p>
        </div>
        <!-- Additional information about ASD -->
        <div class="container">
            <h2>About Autism Spectrum Disorder</h2>
            <p>Autism Spectrum Disorder (ASD) is a neurodevelopmental condition characterized by challenges with social communication, restricted interests, and repetitive behaviors.</p>
            <p>Early screening and support can help individuals with ASD reach their full potential. This tool provides an initial screening and guidance only and is not a substitute for professional medical advice.</p>
        </div>
    </main>
</body>
</html>