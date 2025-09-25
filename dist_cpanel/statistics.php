<?php
require_once 'api/config.php';

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

switch ($method) {
    case 'GET':
        handleGetStatistics();
        break;
    default:
        jsonResponse(false, 'Method not allowed', null, 405);
}

function handleGetStatistics() {
    try {
        $db = getDatabase();
        
        // Get total count
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM bid_requests");
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get counts by status
        $statuses = ['new', 'reviewing', 'quoted', 'accepted', 'rejected'];
        $counts = [];
        
        foreach ($statuses as $status) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM bid_requests WHERE status = ?");
            $stmt->execute([$status]);
            $counts[$status] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        }
        
        $statistics = [
            'total' => (int)$total,
            'new' => (int)$counts['new'],
            'reviewing' => (int)$counts['reviewing'],
            'quoted' => (int)$counts['quoted'],
            'accepted' => (int)$counts['accepted'],
            'rejected' => (int)$counts['rejected']
        ];
        
        jsonResponse(true, 'Statistics retrieved successfully', $statistics);
        
    } catch (Exception $e) {
        error_log("Error fetching statistics: " . $e->getMessage());
        jsonResponse(false, 'Failed to fetch statistics', null, 500);
    }
}
?>
