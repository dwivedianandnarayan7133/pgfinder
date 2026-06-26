<?php
/**
 * API: Get Shortlist
 * Returns the logged-in user's shortlisted properties as JSON.
 * Consumed by the React shortlist component.
 */

require_once __DIR__ . '/../config/db.php';
startSession();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.', 'data' => []]);
    exit;
}

$userId = currentUserId();
$db     = getDB();

$stmt = $db->prepare("
    SELECT
        p.id, p.name, p.city, p.address,
        p.price, p.gender, p.rating, p.image,
        iu.created_at AS saved_at
    FROM interested_users iu
    JOIN properties p ON p.id = iu.property_id
    WHERE iu.user_id = :uid
    ORDER BY iu.created_at DESC
");
$stmt->execute([':uid' => $userId]);
$shortlist = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $shortlist]);
