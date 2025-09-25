<?php
require_once 'config.php';
require_once 'email.php';

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

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
        'phone' => ['phone' => true],
        'projectType' => [
            'required' => true,
            'options' => ['brand-identity', 'web-design', 'print-design', 'digital-marketing', 'ui-ux', 'packaging', 'other']
        ],
        'projectTitle' => ['required' => true, 'min_length' => 1],
        'description' => ['required' => true, 'min_length' => 10],
        'budget' => [
            'required' => true,
            'options' => ['under-5k', '5k-10k', '10k-25k', '25k-50k', '50k-100k', 'over-100k']
        ],
        'timeline' => [
            'required' => true,
            'options' => ['asap', '1-month', '2-3-months', '3-6-months', '6-months-plus']
        ],
        'referral' => [
            'options' => ['search', 'social', 'referral', 'portfolio', 'other']
        ]
    ];

    // Validate input
    $errors = validateInput($data, $rules);

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

        // Prepare email data
        $emailData = array_merge($data, [
            'id' => $bidId,
            'services' => isset($data['services']) ? $data['services'] : []
        ]);

        // Send emails
        $emailResults = sendBidRequestEmails($emailData);

        jsonResponse(true, 'Bid request submitted successfully', [
            'bidId' => $bidId,
            'emailSent' => $emailResults
        ]);

    } catch (Exception $e) {
        error_log('Bid request submission error: ' . $e->getMessage());
        jsonResponse(false, 'Failed to save bid request', null, 500);
    }
}

// Handle getting bid requests (admin)
function handleGetBidRequests() {
    try {
        $pdo = getDatabase();

        // Check if requesting specific bid
        $pathInfo = $_SERVER['PATH_INFO'] ?? '';
        if (preg_match('/^\/(\d+)$/', $pathInfo, $matches)) {
            // Get specific bid request
            $id = $matches[1];
            $sql = "SELECT * FROM bid_requests WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch();

            if (!$result) {
                jsonResponse(false, 'Bid request not found', null, 404);
            }

            // Parse services JSON
            $result['services'] = json_decode($result['services'] ?? '[]', true);

            jsonResponse(true, 'Bid request retrieved successfully', $result);
        } else {
            // Get all bid requests
            $sql = "SELECT * FROM bid_requests ORDER BY created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();

            // Parse services JSON for each result
            foreach ($results as &$result) {
                $result['services'] = json_decode($result['services'] ?? '[]', true);
            }

            jsonResponse(true, 'Bid requests retrieved successfully', $results);
        }

    } catch (Exception $e) {
        error_log('Bid requests retrieval error: ' . $e->getMessage());
        jsonResponse(false, 'Failed to retrieve bid requests', null, 500);
    }
}

// Handle updating bid request status
function handleUpdateBidRequest() {
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';

    if (!preg_match('/^\/(\d+)\/status$/', $pathInfo, $matches)) {
        jsonResponse(false, 'Invalid endpoint', null, 400);
    }

    $id = $matches[1];
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

        jsonResponse(true, 'Status updated successfully');

    } catch (Exception $e) {
        error_log('Status update error: ' . $e->getMessage());
        jsonResponse(false, 'Failed to update status', null, 500);
    }
}
?>