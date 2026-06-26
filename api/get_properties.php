<?php
/**
 * API: Get Properties
 * Returns JSON array of properties filtered by city, gender, min/max price.
 * Supports AJAX calls from the frontend filter panel.
 */

require_once __DIR__ . '/../config/db.php';
startSession();

header('Content-Type: application/json');
header('Cache-Control: no-cache');

$db = getDB();

// Sanitise inputs
$city     = trim($_GET['city']     ?? '');
$gender   = trim($_GET['gender']   ?? '');
$minPrice = (int)($_GET['min_price'] ?? 0);
$maxPrice = (int)($_GET['max_price'] ?? 99999);
$search   = trim($_GET['search']   ?? '');

// Build query dynamically
$where  = ["p.price BETWEEN :min AND :max", "p.status = 'approved'"];
$params = [':min' => $minPrice, ':max' => $maxPrice];

if ($city !== '' && $city !== 'all') {
    $where[]        = 'LOWER(p.city) = LOWER(:city)';
    $params[':city'] = $city;
}

if (in_array($gender, ['male', 'female', 'any'])) {
    $where[]          = 'p.gender = :gender';
    $params[':gender'] = $gender;
}

if ($search !== '') {
    $where[]            = '(p.name LIKE :search OR p.city LIKE :search OR p.address LIKE :search)';
    $params[':search']  = '%' . $search . '%';
}

$whereSQL = implode(' AND ', $where);

$sql = "
    SELECT
        p.id, p.name, p.city, p.address,
        p.price, p.gender, p.rating, p.image,
        GROUP_CONCAT(a.name SEPARATOR ', ') AS amenities_preview
    FROM properties p
    LEFT JOIN property_amenities pa ON pa.property_id = p.id
    LEFT JOIN amenities a           ON a.id = pa.amenity_id
    WHERE $whereSQL
    GROUP BY p.id
    ORDER BY p.rating DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();

// Add interest flag for logged-in users
$userId = currentUserId();
if ($userId) {
    $intStmt = $db->prepare(
        'SELECT property_id FROM interested_users WHERE user_id = :uid'
    );
    $intStmt->execute([':uid' => $userId]);
    $interested = array_column($intStmt->fetchAll(), 'property_id');

    foreach ($properties as &$p) {
        $p['is_interested'] = in_array($p['id'], $interested);
    }
    unset($p);
} else {
    foreach ($properties as &$p) {
        $p['is_interested'] = false;
    }
    unset($p);
}

echo json_encode(['success' => true, 'data' => $properties]);
