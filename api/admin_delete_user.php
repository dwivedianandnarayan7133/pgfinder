<?php
require_once '../config/db.php';
startSession();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Admin access required.']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'POST required.']); exit;
}

$userId = (int)($_POST['user_id'] ?? 0);
if ($userId <= 0) {
    echo json_encode(['success'=>false,'message'=>'Invalid user ID.']); exit;
}
// Prevent deleting yourself or other admins
if ($userId === (int)$_SESSION['admin_id']) {
    echo json_encode(['success'=>false,'message'=>'You cannot delete your own account.']); exit;
}

$db = getDB();
$role = $db->prepare("SELECT role FROM users WHERE id=:id");
$role->execute([':id'=>$userId]);
$row = $role->fetch();
if (!$row) { echo json_encode(['success'=>false,'message'=>'User not found.']); exit; }
if ($row['role'] === 'admin') { echo json_encode(['success'=>false,'message'=>'Cannot delete admin accounts.']); exit; }

$db->prepare("DELETE FROM users WHERE id=:id")->execute([':id'=>$userId]);
echo json_encode(['success'=>true,'message'=>'User deleted.']);
