<?php
// Database configuration for cPanel hosting
// Update these values with your cPanel MySQL database details
define('DB_HOST', 'localhost'); // Usually localhost for cPanel
define('DB_NAME', 'your_database_name'); // Your cPanel database name
define('DB_USER', 'your_database_user'); // Your cPanel database username
define('DB_PASS', 'your_database_password'); // Your cPanel database password

// Email configuration for cPanel
define('EMAIL_HOST', 'localhost'); // or your SMTP server
define('EMAIL_PORT', 587);
define('EMAIL_USER', 'noreply@yourdomain.com'); // Your domain email
define('EMAIL_PASS', 'your_email_password'); // Your email password
define('EMAIL_SECURE', true); // true for SSL, false for TLS

// Admin configuration
define('ADMIN_EMAIL', 'admin@yourdomain.com'); // Your admin email
define('ADMIN_NAME', 'Steven Design Co');

// Security configuration
define('RATE_LIMIT_WINDOW', 900); // 15 minutes in seconds
define('RATE_LIMIT_MAX_REQUESTS', 5); // Max requests per window per IP

// Helper functions
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
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

function jsonResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function checkRateLimit($ip) {
    try {
        $db = getDatabase();
        
        // Clean old entries
        $db->prepare("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)")
           ->execute([RATE_LIMIT_WINDOW]);
        
        // Check current requests
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM rate_limits WHERE ip_address = ?");
        $stmt->execute([$ip]);
        $count = $stmt->fetch()['count'];
        
        if ($count >= RATE_LIMIT_MAX_REQUESTS) {
            jsonResponse(false, 'Rate limit exceeded. Please try again later.', null, 429);
        }
        
        // Record this request
        $db->prepare("INSERT INTO rate_limits (ip_address) VALUES (?)")
           ->execute([$ip]);
           
    } catch (Exception $e) {
        // If rate limiting fails, continue (don't block the request)
        error_log("Rate limiting error: " . $e->getMessage());
    }
}
?>
