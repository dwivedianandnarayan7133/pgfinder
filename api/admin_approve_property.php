<?php
require_once __DIR__ . '/../config/db.php';
startSession();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Admin access required.']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'POST required.']); exit;
}

$propertyId = (int)($_POST['property_id'] ?? 0);
$action     = $_POST['action'] ?? '';

if ($propertyId <= 0 || !in_array($action, ['approved','rejected','pending'])) {
    echo json_encode(['success'=>false,'message'=>'Invalid data.']); exit;
}

$db = getDB();
$db->prepare("UPDATE properties SET status=:s WHERE id=:id")
   ->execute([':s'=>$action, ':id'=>$propertyId]);

$msg = ['approved'=>'Property approved and now live!', 'rejected'=>'Property rejected.', 'pending'=>'Property set to pending.'];
echo json_encode(['success'=>true,'message'=>$msg[$action],'status'=>$action]);
