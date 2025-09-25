<?php
require_once 'api/config.php';

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

switch ($method) {
    case 'GET':
        handleGetBidRequests();
        break;
    default:
        jsonResponse(false, 'Method not allowed', null, 405);
}

function handleGetBidRequests() {
    try {
        $db = getDatabase();
        
        // Get all bid requests ordered by newest first
        $stmt = $db->prepare("
            SELECT 
                id, first_name as firstName, last_name as lastName, email, phone, company, 
                project_type as projectType, project_title as projectTitle, description, budget, 
                timeline, services, referral, status, 
                created_at, updated_at
            FROM bid_requests 
            ORDER BY created_at DESC
        ");
        
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format services as array
        foreach ($requests as &$request) {
            $request['services'] = json_decode($request['services'], true) ?: [];
        }
        
        jsonResponse(true, 'Bid requests retrieved successfully', $requests);
        
    } catch (Exception $e) {
        error_log("Error fetching bid requests: " . $e->getMessage());
        jsonResponse(false, 'Failed to fetch bid requests', null, 500);
    }
}
?>
