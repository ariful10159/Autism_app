<?php
require_once __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Verify the uploaded file exists
if (!isset($_FILES['face']) || $_FILES['face']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Please upload a valid image.']);
    exit;
}

// Validate file type (basic check)
$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
$fileType = mime_content_type($_FILES['face']['tmp_name']);
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Unsupported file type. Please upload JPG or PNG.']);
    exit;
}

// Create uploads directory if not exists
$uploadsDir = __DIR__ . '/uploads';
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}
// Generate unique file name
$fileName = 'face_' . time() . '_' . bin2hex(random_bytes(4));
$extension = pathinfo($_FILES['face']['name'], PATHINFO_EXTENSION);
$targetPath = $uploadsDir . '/' . $fileName . '.' . $extension;

// Move uploaded file
if (!move_uploaded_file($_FILES['face']['tmp_name'], $targetPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded image.']);
    exit;
}

// Skip model inference: we only store the uploaded image and rely on
// questionnaire responses for evaluation. Use placeholder values.
$userId = $_SESSION['user_id'];
// Save detection result with zero probability and placeholder result
save_detection_result($userId, 0.0, 'Uploaded');

// Always return pending: classification happens only after questionnaire
echo json_encode([
    'status' => 'pending',
    'message' => 'Image received. Please complete the questionnaire to view the result.'
]);
exit;