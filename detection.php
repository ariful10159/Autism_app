<?php
require_once __DIR__ . '/config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Detection - ASD Detection App</title>
    <link rel="stylesheet" href="assets/styles.css">
    <script src="assets/script.js" defer></script>
</head>
<body>
    <!-- New mobile header with hamburger menu -->
    <input type="checkbox" id="menu-toggle" />
    <div class="mobile-header">
        <div class="logo-name">
            <img src="assets/autism1.png" alt="ASD art">
            <span>Face Detection</span>
        </div>
        <label for="menu-toggle" class="burger" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </label>
    </div>
    <!-- Slide-down navigation links -->
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
            <h2>Upload or Capture a Face Photo</h2>
            <p>Please upload a clear photo of your face. The image will be sent to our model for ASD probability evaluation.</p>
            <form id="detection-form" enctype="multipart/form-data" method="post" onsubmit="submitDetection(event)">
                <label>Select Image:</label>
                <input type="file" name="face" id="file-input" accept="image/*" onchange="previewImage(this)" required>
                <button type="submit">Detect ASD/TD</button>
            </form>
            <p style="margin-top:0.5rem;">OR</p>
            <button type="button" onclick="openCamera()">Open Camera</button>
            <div id="camera-container" style="display:none; margin-top:1rem;">
                <video id="camera-video" width="300" height="225" autoplay playsinline style="border:1px solid #ccc; border-radius:4px;"></video>
                <div style="margin-top:0.5rem;">
                    <button type="button" onclick="takePhoto()">Capture</button>
                    <button type="button" onclick="closeCamera()">Close Camera</button>
                </div>
            </div>
            <canvas id="camera-canvas" width="300" height="225" style="display:none;"></canvas>
            <div id="preview" class="image-preview">
                <img src="" alt="Image preview">
            </div>
            <div id="detection-result" style="margin-top: 1rem;"></div>
        </div>
    </main>
</body>
</html>