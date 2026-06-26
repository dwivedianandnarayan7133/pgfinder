<?php
/**
 * test_db.php — Live Database and Session Diagnostic Tool (Query Tester)
 */
require_once __DIR__ . '/config/db.php';

header('Content-Type: text/plain');

echo "--- PGFINDER DIAGNOSTIC TOOL ---\n\n";

try {
    echo "Connecting to database...\n";
    $pdo = getDB();
    echo "✅ Database connection SUCCESS!\n\n";

    echo "Running exact properties query...\n";

    $minPrice = 0;
    $maxPrice = 99999;
    $where  = ["p.price BETWEEN :min AND :max", "p.status = 'approved'"];
    $params = [':min' => $minPrice, ':max' => $maxPrice];

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

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();

    echo "✅ Query executed SUCCESS!\n";
    echo "Properties count: " . count($properties) . "\n\n";

    if (count($properties) > 0) {
        echo "Sample Property:\n";
        print_r($properties[0]);
    }

} catch (Exception $e) {
    echo "❌ ERROR encountered during query: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
