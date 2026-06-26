<?php
define('DB_HOST','localhost');
define('DB_NAME','student_accommodation');
define('DB_USER','root');
define('DB_PASS','Shiv@241');
define('DB_CHARSET','utf8mb4');

try {
    $pdo = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET,
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Connected OK.\n";
    echo "Tables: " . (empty($tables) ? 'NONE FOUND' : implode(', ', $tables)) . "\n";

    if (in_array('properties', $tables)) {
        $count = $pdo->query('SELECT COUNT(*) FROM properties')->fetchColumn();
        echo "Properties count: $count\n";
    } else {
        echo "ERROR: 'properties' table does not exist!\n";
    }
} catch(Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
