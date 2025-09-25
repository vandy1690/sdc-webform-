<?php
require_once 'config.php';

// Handle GET request for statistics
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

try {
    $pdo = getDatabase();

    $sql = "SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
        SUM(CASE WHEN status = 'reviewing' THEN 1 ELSE 0 END) as reviewing_count,
        SUM(CASE WHEN status = 'quoted' THEN 1 ELSE 0 END) as quoted_count,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_count,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
        FROM bid_requests";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch();

    // Convert string numbers to integers
    foreach ($result as $key => $value) {
        $result[$key] = (int) $value;
    }

    jsonResponse(true, 'Statistics retrieved successfully', $result);

} catch (Exception $e) {
    error_log('Statistics retrieval error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to retrieve statistics', null, 500);
}
?>