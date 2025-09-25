<?php
require_once 'api/config-cpanel.php';
require_once 'api/email.php';

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

// Apply rate limiting for POST requests
if ($method === 'POST') {
    checkRateLimit(getClientIP());
}

switch ($method) {
    case 'POST':
        handleSubmitBidRequest();
        break;
    case 'GET':
        handleGetBidRequests();
        break;
    case 'PUT':
        handleUpdateBidRequest();
        break;
    default:
        jsonResponse(false, 'Method not allowed', null, 405);
}

// Handle bid request submission
function handleSubmitBidRequest() {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        jsonResponse(false, 'Invalid JSON input', null, 400);
    }

    // Sanitize input
    $data = sanitizeInput($input);

    // Validation rules
    $rules = [
        'firstName' => ['required' => true, 'min_length' => 1],
        'lastName' => ['required' => true, 'min_length' => 1],
        'email' => ['required' => true, 'email' => true],
        'projectType' => ['required' => true],
        'projectTitle' => ['required' => true, 'min_length' => 1],
        'description' => ['required' => true, 'min_length' => 10],
        'budget' => ['required' => true],
        'timeline' => ['required' => true]
    ];

    // Validate input
    $errors = [];
    foreach ($rules as $field => $rule) {
        if ($rule['required'] && empty($data[$field])) {
            $errors[] = ucfirst($field) . ' is required';
        } elseif (!empty($data[$field])) {
            if (isset($rule['min_length']) && strlen($data[$field]) < $rule['min_length']) {
                $errors[] = ucfirst($field) . ' must be at least ' . $rule['min_length'] . ' characters';
            }
            if (isset($rule['email']) && $rule['email'] && !validateEmail($data[$field])) {
                $errors[] = 'Valid email is required';
            }
        }
    }

    if (!empty($errors)) {
        jsonResponse(false, 'Validation failed', ['errors' => $errors], 400);
    }

    try {
        $pdo = getDatabase();

        // Prepare services data
        $services = isset($data['services']) && is_array($data['services']) ? json_encode($data['services']) : '[]';

        // Insert bid request
        $sql = "INSERT INTO bid_requests
                (first_name, last_name, email, phone, company, project_type,
                 project_title, description, budget, timeline, services, referral, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', NOW(), NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['firstName'],
            $data['lastName'],
            $data['email'],
            $data['phone'] ?? null,
            $data['company'] ?? null,
            $data['projectType'],
            $data['projectTitle'],
            $data['description'],
            $data['budget'],
            $data['timeline'],
            $services,
            $data['referral'] ?? null
        ]);

        $bidId = $pdo->lastInsertId();

        // Send emails
        $emailSent = sendBidRequestEmails($data, $bidId);

        jsonResponse(true, 'Bid request submitted successfully', [
            'bidId' => $bidId,
            'emailSent' => $emailSent
        ]);

    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        jsonResponse(false, 'Failed to submit bid request', null, 500);
    }
}

// Handle getting bid requests
function handleGetBidRequests() {
    try {
        $pdo = getDatabase();

        // Check if requesting specific bid
        $pathInfo = $_SERVER['PATH_INFO'] ?? '';
        if (preg_match('/^\/(\d+)$/', $pathInfo, $matches)) {
            // Get specific bid request
            $id = $matches[1];
            $stmt = $pdo->prepare("SELECT * FROM bid_requests WHERE id = ?");
            $stmt->execute([$id]);
            $request = $stmt->fetch();
            
            if (!$request) {
                jsonResponse(false, 'Bid request not found', null, 404);
            }
            
            jsonResponse(true, 'Bid request retrieved successfully', $request);
        } else {
            // Get all bid requests
            $stmt = $pdo->prepare("SELECT * FROM bid_requests ORDER BY created_at DESC");
            $stmt->execute();
            $requests = $stmt->fetchAll();
            
            jsonResponse(true, 'Bid requests retrieved successfully', $requests);
        }

    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        jsonResponse(false, 'Failed to retrieve bid requests', null, 500);
    }
}

// Handle updating bid request status
function handleUpdateBidRequest() {
    // Get ID from query parameter
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        jsonResponse(false, 'ID is required', null, 400);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['status'])) {
        jsonResponse(false, 'Status is required', null, 400);
    }

    $status = sanitizeInput($input['status']);
    $validStatuses = ['new', 'reviewing', 'quoted', 'accepted', 'rejected'];

    if (!in_array($status, $validStatuses)) {
        jsonResponse(false, 'Invalid status', null, 400);
    }

    try {
        $pdo = getDatabase();

        $sql = "UPDATE bid_requests SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $id]);

        if ($stmt->rowCount() === 0) {
            jsonResponse(false, 'Bid request not found', null, 404);
        }

        jsonResponse(true, 'Status updated successfully', ['id' => $id, 'status' => $status]);

    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        jsonResponse(false, 'Failed to update status', null, 500);
    }
}
?>
