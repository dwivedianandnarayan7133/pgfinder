<?php
/**
 * API: Toggle Interest
 * Marks or unmarks a property as "interested" for the logged-in user.
 * Expects POST: property_id
 * Returns: JSON {success, is_interested, message}
 */

require_once __DIR__ . '/../config/db.php';
startSession();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to mark interest.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$propertyId = (int)($_POST['property_id'] ?? 0);
$userId     = currentUserId();

if ($propertyId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid property ID.']);
    exit;
}

$db = getDB();

// Check if already interested
$checkStmt = $db->prepare(
    'SELECT id FROM interested_users WHERE user_id = :uid AND property_id = :pid'
);
$checkStmt->execute([':uid' => $userId, ':pid' => $propertyId]);
$existing = $checkStmt->fetch();

if ($existing) {
    // Remove interest
    $delStmt = $db->prepare(
        'DELETE FROM interested_users WHERE user_id = :uid AND property_id = :pid'
    );
    $delStmt->execute([':uid' => $userId, ':pid' => $propertyId]);
    echo json_encode([
        'success'      => true,
        'is_interested' => false,
        'message'      => 'Removed from shortlist.'
    ]);
} else {
    // Add interest
    $insStmt = $db->prepare(
        'INSERT INTO interested_users (user_id, property_id) VALUES (:uid, :pid)'
    );
    $insStmt->execute([':uid' => $userId, ':pid' => $propertyId]);
    echo json_encode([
        'success'      => true,
        'is_interested' => true,
        'message'      => 'Added to shortlist!'
    ]);
}
