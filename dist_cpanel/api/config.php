<?php
// Database configuration
define('DB_HOST', 'db');
define('DB_NAME', 'db');
define('DB_USER', 'db');
define('DB_PASS', 'db');

// Email configuration
define('EMAIL_HOST', 'localhost'); // or your SMTP server
define('EMAIL_PORT', 587);
define('EMAIL_USER', 'noreply@sdc-webform.ddev.site');
define('EMAIL_PASS', 'password');
define('EMAIL_SECURE', true); // true for SSL, false for TLS

// Admin configuration
define('ADMIN_EMAIL', 'admin@sdc-webform.ddev.site');
define('ADMIN_NAME', 'SDC Creative Studio');

// Security configuration
define('RATE_LIMIT_WINDOW', 900); // 15 minutes in seconds
define('RATE_LIMIT_MAX', 5); // max requests per window

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Database connection function
function getDatabase() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
        exit;
    }
}

// Rate limiting function
function checkRateLimit($ip) {
    $pdo = getDatabase();

    // Clean old entries
    $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([RATE_LIMIT_WINDOW]);

    // Count current requests
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rate_limits WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$ip, RATE_LIMIT_WINDOW]);
    $result = $stmt->fetch();

    if ($result['count'] >= RATE_LIMIT_MAX) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'Too many requests from this IP, please try again later.'
        ]);
        exit;
    }

    // Add current request
    $stmt = $pdo->prepare("INSERT INTO rate_limits (ip_address, created_at) VALUES (?, NOW())");
    $stmt->execute([$ip]);
}

// Input validation function
function validateInput($data, $rules) {
    $errors = [];

    foreach ($rules as $field => $rule) {
        $value = isset($data[$field]) ? trim($data[$field]) : '';

        // Check required fields
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            $errors[] = ucfirst($field) . ' is required';
            continue;
        }

        // Skip validation if field is empty and not required
        if (empty($value) && (!isset($rule['required']) || !$rule['required'])) {
            continue;
        }

        // Email validation
        if (isset($rule['email']) && $rule['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }

        // Length validation
        if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
            $errors[] = ucfirst($field) . ' must be at least ' . $rule['min_length'] . ' characters';
        }

        // Options validation
        if (isset($rule['options']) && !in_array($value, $rule['options'])) {
            $errors[] = 'Invalid ' . $field . ' value';
        }

        // Phone validation (basic)
        if (isset($rule['phone']) && $rule['phone'] && !preg_match('/^[\d\s\-\+\(\)\.]+$/', $value)) {
            $errors[] = 'Valid phone number required';
        }
    }

    return $errors;
}

// JSON response function
function jsonResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    $response = [
        'success' => $success,
        'message' => $message
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response);
    exit;
}

// Security function to sanitize input
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Get client IP address
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
?>