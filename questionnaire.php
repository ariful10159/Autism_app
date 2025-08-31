<?php
require_once __DIR__ . '/config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// Define a set of screening questions. These questions are
// illustrative and not a substitute for professional assessment.
$questions = [
    'I often notice small sounds when others do not.', // A1
    'I usually concentrate more on the whole picture rather than the small details.', // A2
    'I find it easy to do more than one thing at once.', // A3
    'If there is an interruption, I can switch back to what I was doing very quickly.', // A4
    'I find it easy to “read between the lines” when someone is talking to me.', // A5
    'I know how to tell if someone listening to me is getting bored.', // A6
    'When I’m reading a story, I find it difficult to work out the characters’ intentions.', // A7
    'I like to collect information about categories of things (e.g., types of cars, birds, trains, plants).', // A8
    'I find it easy to work out what someone is thinking or feeling just by looking at their face.', // A9
    'I find it difficult to work out people’s intentions.' // A10
];

$submitted = false;
$message = '';
$score = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = [];
    // Collect answers from POST
    foreach ($questions as $idx => $question) {
        $key = 'q' . $idx;
        $answer = $_POST[$key] ?? 'no';
        $answers[$idx] = $answer;
    }
    // Calculate score
    $score = calculate_questionnaire_score($answers);
    // Save to database
    save_questionnaire_result($userId, $answers, $score);
    $submitted = true;
    $message = 'Your questionnaire has been submitted. Your score is ' . $score . ' out of ' . count($questions) . '.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionnaire - ASD Detection App</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <!-- Begin responsive header and navigation -->
    <input type="checkbox" id="menu-toggle" />
    <div class="mobile-header">
        <div class="logo-name">
            <img src="assets/autism1.png" alt="ASD art">
            <span>ASD Screening Questionnaire</span>
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
            <?php if ($submitted): ?>
                <p class="success"><?= htmlspecialchars($message) ?></p>
                <p>Please upload a photo to complete the evaluation. After both steps, your results will be available on the <a href="suggestions.php">Suggestions</a> page.</p>
            <?php else: ?>
                <form method="post" action="questionnaire.php">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question">
                            <label><?= htmlspecialchars(($index + 1) . '. ' . $question) ?></label><br>
                            <input type="radio" name="q<?= $index ?>" value="yes" required> Yes
                            <input type="radio" name="q<?= $index ?>" value="no"> No
                        </div>
                    <?php endforeach; ?>
                    <input type="submit" value="Submit Questionnaire">
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>