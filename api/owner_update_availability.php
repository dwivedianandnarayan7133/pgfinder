<?php
require_once __DIR__ . '/../config/db.php';
startSession();
header('Content-Type: application/json');

if (!isLoggedIn() || (!isOwner() && !isAdmin())) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized.']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'POST required.']); exit;
}

$propertyId    = (int)($_POST['property_id']    ?? 0);
$availableRooms = (int)($_POST['available_rooms'] ?? -1);

if ($propertyId <= 0 || $availableRooms < 0) {
    echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit;
}

$db = getDB();

// Verify ownership (admins can edit any)
$where = isAdmin() ? 'id=:pid' : 'id=:pid AND owner_id=:uid';
$params = isAdmin() ? [':pid'=>$propertyId] : [':pid'=>$propertyId, ':uid'=>currentUserId()];

$prop = $db->prepare("SELECT total_rooms FROM properties WHERE $where");
$prop->execute($params);
$row = $prop->fetch();

if (!$row) {
    echo json_encode(['success'=>false,'message'=>'Property not found or not yours.']); exit;
}
if ($availableRooms > $row['total_rooms']) {
    echo json_encode(['success'=>false,'message'=>'Available rooms cannot exceed total rooms ('.$row['total_rooms'].').']); exit;
}

$upd = $db->prepare("UPDATE properties SET available_rooms=:ar WHERE id=:pid");
$upd->execute([':ar'=>$availableRooms, ':pid'=>$propertyId]);

echo json_encode(['success'=>true,'message'=>'Availability updated.','available_rooms'=>$availableRooms]);
