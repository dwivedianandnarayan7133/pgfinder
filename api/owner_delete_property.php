<?php
require_once '../config/db.php';
startSession();
header('Content-Type: application/json');

if (!isLoggedIn() || (!isOwner() && !isAdmin())) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized.']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'POST required.']); exit;
}

$propertyId = (int)($_POST['property_id'] ?? 0);
if ($propertyId <= 0) {
    echo json_encode(['success'=>false,'message'=>'Invalid property ID.']); exit;
}

$db = getDB();

// Verify ownership
$where  = isAdmin() ? 'id=:pid' : 'id=:pid AND owner_id=:uid';
$params = isAdmin() ? [':pid'=>$propertyId] : [':pid'=>$propertyId,':uid'=>currentUserId()];
$check  = $db->prepare("SELECT id FROM properties WHERE $where");
$check->execute($params);
if (!$check->fetch()) {
    echo json_encode(['success'=>false,'message'=>'Property not found or not yours.']); exit;
}

// Delete (cascades to property_amenities and interested_users)
$db->prepare("DELETE FROM properties WHERE id=:pid")->execute([':pid'=>$propertyId]);

echo json_encode(['success'=>true,'message'=>'Property deleted successfully.']);
