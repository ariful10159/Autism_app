<?php
/*
 * Configuration and helper functions for ASD Detection App.
 *
 * This file contains functions to initialize the SQLite database,
 * manage user sessions, handle authentication, and interact with
 * questionnaire and detection results. The database will be stored
 * in the `data` directory relative to this file. If the database
 * does not exist when the application runs for the first time, it
 * will be created automatically along with the necessary tables.
 */

// Path to the SQLite database file
$DATABASE_PATH = __DIR__ . '/data/app.sqlite';

/**
 * Returns a PDO connection to the SQLite database. If the database
 * file does not exist, it initializes the database structure.
 *
 * @return PDO
 */
function get_db() {
    global $DATABASE_PATH;
    // Create data directory if not exists
    $dataDir = dirname($DATABASE_PATH);
    if (!file_exists($dataDir)) {
        mkdir($dataDir, 0777, true);
    }
    // Create PDO connection
    $pdo = new PDO('sqlite:' . $DATABASE_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Initialize tables if they don't exist
    init_db($pdo);
    return $pdo;
}

/**
 * Initializes database tables for users, results and questionnaires.
 *
 * @param PDO $pdo The PDO connection
 */
function init_db($pdo) {
    // Users table
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        profile_photo TEXT,
        age INTEGER,
        gender TEXT,
        jaundice TEXT,
        family_history TEXT
    )');
    // Detection results table
    $pdo->exec('CREATE TABLE IF NOT EXISTS results (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        probability REAL NOT NULL,
        result TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )');
    // Questionnaire answers table
    $pdo->exec('CREATE TABLE IF NOT EXISTS questionnaires (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        answers TEXT NOT NULL,
        score INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )');

    // Attempt to add new columns if they do not exist (for upgrades)
    $columns = [
        'profile_photo TEXT',
        'age INTEGER',
        'gender TEXT',
        'jaundice TEXT',
        'family_history TEXT'
    ];
    foreach ($columns as $col) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN $col");
        } catch (Exception $e) {
            // Column may already exist; ignore errors
        }
    }
}

/**
 * Registers a new user with the provided name, email and password.
 * Returns true on success or an error message string on failure.
 * Passwords are hashed using password_hash() with default algorithm.
 *
 * @param string $name
 * @param string $email
 * @param string $password
 * @return mixed
 */
function register_user($name, $email, $password) {
    $pdo = get_db();
    // Check for existing email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        return 'This email is already registered.';
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, profile_photo) VALUES (:name, :email, :password, :profile_photo)');
    $success = $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':password' => $hash,
        ':profile_photo' => null
    ]);
    return $success;
}

/**
 * Attempts to log in a user with the given email and password.
 * On success, the user's ID will be stored in the session. Returns
 * true on success or false on failure.
 *
 * @param string $email
 * @param string $password
 * @return bool
 */
function login_user($email, $password) {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, password FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

/**
 * Logs out the current user by clearing the session.
 */
function logout_user() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    session_destroy();
}

/**
 * Returns the currently logged in user's data as an associative array
 * or null if no user is logged in.
 *
 * @return array|null
 */
function current_user() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, name, email, created_at, profile_photo FROM users WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Saves a detection result for the specified user.
 *
 * @param int $userId
 * @param float $probability Probability (0.0 to 1.0) of ASD
 * @param string $result The classification result (e.g. "ASD positive")
 */
function save_detection_result($userId, $probability, $result) {
    $pdo = get_db();
    $stmt = $pdo->prepare('INSERT INTO results (user_id, probability, result) VALUES (:user_id, :probability, :result)');
    $stmt->execute([
        ':user_id' => $userId,
        ':probability' => $probability,
        ':result' => $result
    ]);
}

/**
 * Saves a questionnaire result for the specified user.
 *
 * @param int $userId
 * @param array $answers List of answers provided by the user
 * @param int $score Calculated score from the questionnaire
 */
function save_questionnaire_result($userId, $answers, $score) {
    $pdo = get_db();
    $stmt = $pdo->prepare('INSERT INTO questionnaires (user_id, answers, score) VALUES (:user_id, :answers, :score)');
    $stmt->execute([
        ':user_id' => $userId,
        ':answers' => json_encode($answers),
        ':score' => $score
    ]);
}

/**
 * Retrieves the latest detection result for a user.
 *
 * @param int $userId
 * @return array|null
 */
function get_latest_detection($userId) {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM results WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Retrieves the latest questionnaire result for a user.
 *
 * @param int $userId
 * @return array|null
 */
function get_latest_questionnaire($userId) {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM questionnaires WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Calculates a score for the questionnaire answers. You can adjust
 * this function to refine how scores are computed. Currently, it
 * assigns one point for each "Yes" answer. The list of questions
 * should correspond to the boolean array of answers.
 *
 * @param array $answers
 * @return int
 */
function calculate_questionnaire_score($answers) {
    $score = 0;
    foreach ($answers as $answer) {
        if (strtolower($answer) === 'yes' || $answer === 1 || $answer === true) {
            $score++;
        }
    }
    return $score;
}

/**
 * Determines suggestions for the user based on the latest detection
 * and questionnaire results. The suggestions provided here are
 * generalized and for demonstration purposes only. In a real-world
 * system you might integrate professional guidelines or resources.
 *
 * @param int $userId
 * @return array List of suggestion strings
 */
function generate_suggestions($userId) {
    $suggestions = [];
    $latestDetection = get_latest_detection($userId);
    $latestQuestionnaire = get_latest_questionnaire($userId);
    // Only use questionnaire data to derive suggestions. The detection
    // image is used solely to ensure that both inputs are provided; it
    // does not influence suggestions or classification. The questionnaire
    // score determines risk levels.
    $score = $latestQuestionnaire['score'] ?? null;
    // Suggestion logic based on questionnaire score
    if ($score !== null) {
        if ($score >= 7) {
            $suggestions[] = 'Your questionnaire responses indicate a higher risk. Schedule an appointment with a healthcare professional.';
        } elseif ($score >= 4) {
            $suggestions[] = 'Your questionnaire responses show some risk factors. Keep track of any behavioral changes and discuss them with a trusted adult or doctor.';
        } else {
            $suggestions[] = 'Your questionnaire responses indicate minimal risk. Continue healthy development and activities.';
        }
    }
    // Generic suggestions
    $suggestions[] = 'Engage in regular physical and social activities to support overall development.';
    $suggestions[] = 'Seek support from family members, friends or local support groups if you have concerns about ASD.';
    return $suggestions;
}
