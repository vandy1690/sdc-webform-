<?php
require_once 'config.php';

// Handle GET request for health check
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

try {
    // Test database connection
    $pdo = getDatabase();

    jsonResponse(true, 'Server is running', [
        'timestamp' => date('c'),
        'php_version' => phpversion(),
        'database_connected' => true,
        'server' => $_SERVER['SERVER_NAME'] ?? 'localhost'
    ]);

} catch (Exception $e) {
    error_log('Health check error: ' . $e->getMessage());
    jsonResponse(false, 'Server error', [
        'timestamp' => date('c'),
        'database_connected' => false
    ], 500);
}
?>