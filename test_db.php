<?php
/**
 * test_db.php — Live Database and Session Diagnostic Tool
 */
require_once __DIR__ . '/config/db.php';

header('Content-Type: text/plain');

echo "--- PGFINDER DIAGNOSTIC TOOL ---\n\n";

// 1. Check PHP Version
echo "PHP Version: " . phpversion() . "\n";

// 2. Check environment variables
echo "DB Host Env: " . (getenv('DB_HOST') ? getenv('DB_HOST') : "[NOT SET]") . "\n";
echo "DB Name Env: " . (getenv('DB_NAME') ? getenv('DB_NAME') : "[NOT SET]") . "\n";
echo "DB User Env: " . (getenv('DB_USER') ? getenv('DB_USER') : "[NOT SET]") . "\n";
echo "DB Pass Env: " . (getenv('DB_PASS') ? "******" : "[NOT SET]") . "\n\n";

try {
    // 3. Test Connection
    echo "Connecting to database...\n";
    $pdo = getDB();
    echo "✅ Database connection SUCCESS!\n\n";

    // 4. List Tables
    echo "Checking tables...\n";
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Found Tables: " . (empty($tables) ? 'NONE FOUND' : implode(', ', $tables)) . "\n\n";

    // 5. Test Sessions Table Structure
    if (in_array('sessions', $tables)) {
        echo "Checking 'sessions' table columns...\n";
        $columns = $pdo->query("SHOW COLUMNS FROM sessions")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo " - {$col['Field']} ({$col['Type']})\n";
        }
        echo "\n";

        // 6. Test Write to Sessions Table
        echo "Testing WRITE query to 'sessions' table...\n";
        $testId = 'test_session_id_' . rand(1000, 9999);
        $testData = 'test_data_content';
        $testTime = time();

        $stmt = $pdo->prepare("REPLACE INTO sessions (id, data, timestamp) VALUES (:id, :data, :timestamp)");
        $writeSuccess = $stmt->execute([
            ':id'        => $testId,
            ':data'      => $testData,
            ':timestamp' => $testTime
        ]);

        if ($writeSuccess) {
            echo "✅ WRITE query succeeded!\n";

            // 7. Test Read from Sessions Table
            echo "Testing READ query from 'sessions' table...\n";
            $stmt = $pdo->prepare("SELECT data FROM sessions WHERE id = :id");
            $stmt->execute([':id' => $testId]);
            $result = $stmt->fetchColumn();

            if ($result === $testData) {
                echo "✅ READ query succeeded and matches content!\n";
            } else {
                echo "❌ READ query failed or mismatch (got: '$result')\n";
            }

            // Cleanup
            $pdo->prepare("DELETE FROM sessions WHERE id = :id")->execute([':id' => $testId]);
        } else {
            echo "❌ WRITE query failed!\n";
        }
    } else {
        echo "❌ ERROR: 'sessions' table does not exist in the database!\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR encountered: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
