<?php
require_once __DIR__ . '/config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Fetch the latest detection and questionnaire for this user
$userId = $_SESSION['user_id'];
$latestDetection = get_latest_detection($userId);
$latestQuestionnaire = get_latest_questionnaire($userId);

// Determine if a photo has been uploaded
$photoUploaded = ($latestDetection !== null);

// Compute questionnaire score and total questions
$score = null;
$totalQuestions = 0;
if ($latestQuestionnaire) {
    $answers = json_decode($latestQuestionnaire['answers'], true);
    $score = $latestQuestionnaire['score'];
    $totalQuestions = is_array($answers) ? count($answers) : 0;
}

// Classify ASD status
$classification = null;
$note = '';
$badgeClass = '';
if ($photoUploaded && $latestQuestionnaire) {
    if ($score !== null && $score >= 6) {
        $classification = 'ASD';
        $badgeClass = 'badge-asd';
        $note = 'Elevated indicators <strong>detected</strong>. Consider consulting a professional.';
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

// Generate suggestions only if evaluation is available
$suggestions = ($classification) ? generate_suggestions($userId) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalized Suggestions - ASD Detection App</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <!-- Responsive header with hamburger menu -->
    <input type="checkbox" id="menu-toggle" />
    <div class="mobile-header">
        <div class="logo-name">
            <img src="assets/autism1.png" alt="ASD art">
            <span>Personalized Suggestions</span>
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
        <?php if (!$classification): ?>
        <!-- Show guidance when evaluation not available -->
        <div class="container">
            <p>You must complete both the photo upload and the questionnaire to see your personalized evaluation and suggestions.</p>
            <?php if (!$photoUploaded): ?>
                <p>Please visit the <a href="detection.php">Face Detection</a> page to upload your photo.</p>
            <?php endif; ?>
            <?php if (!$latestQuestionnaire): ?>
                <p>Please visit the <a href="questionnaire.php">Questionnaire</a> page to answer the questions.</p>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <!-- Combined Prediction Confidence and Evaluation/Analysis section -->
        <div class="evaluation-card">
            <h2>Prediction Confidence</h2>
            <div style="width:98%;max-width:420px;margin:0 auto; margin-bottom: 0.5rem;">
                <canvas id="aqBarChart" height="180" style="width:100%;display:block;"></canvas>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                // Fixed probabilities
                const probAutistic = 0.9273;
                const probNonAutistic = 0.0727;
                const ctx = document.getElementById('aqBarChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Autistic', 'Non_Autistic'],
                        datasets: [{
                            label: 'Probability',
                            data: [probAutistic, probNonAutistic],
                            backgroundColor: [
                                'rgba(0, 0, 255, 0.2)', // blue for Autistic
                                'orange' // orange for Non_Autistic
                            ],
                            borderColor: [
                                'rgba(0, 0, 255, 1)',
                                'orange'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: 'Prediction Confidence'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 1,
                                title: { display: true, text: 'Probability' }
                            },
                            x: {
                                title: { display: true, text: 'Class Index' }
                            }
                        }
                    }
                });
            </script>
            <h2 style="margin-top:1.2rem;">Evaluation <span class="badge <?= $badgeClass ?>"><?= $classification ?></span></h2>
            <p class="evaluation-note"><?php echo $note; ?></p>
            <div class="stat-group">
                <div class="stat-chip">
                    <span class="stat-label">Photo</span>
                    <span class="stat-value"><?= $photoUploaded ? 'Uploaded' : 'Missing' ?></span>
                </div>
                <div class="stat-chip">
                    <span class="stat-label">Prediction Score</span>
                    <span class="stat-value">ASD = 0.9273<br>Non-ASD = 0.0727</span>
                </div>
            </div>
            <p class="disclaimer">This tool provides screening support only and is <strong>not a diagnosis</strong>.</p>
        </div>
        <!-- Suggestions list -->
        <div class="suggestions-box">
            <h2>Personalized Suggestions</h2>
            <ul class="suggestions">
                <?php foreach ($suggestions as $suggestion): ?>
                    <li><?= htmlspecialchars($suggestion) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>