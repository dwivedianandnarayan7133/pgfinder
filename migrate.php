<?php
/**
 * migrate.php — Database Migration
 * Adds owner/admin roles, room availability, property status
 * Run once: http://localhost/student-accommodation/migrate.php
 */

$host    = getenv('DB_HOST') ?: 'localhost';
$user    = getenv('DB_USER') ?: 'root';
$pass    = getenv('DB_PASS') ?: 'Shiv@241';
$dbName  = getenv('DB_NAME') ?: 'student_accommodation';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';

$log = []; $success = true;

function logMsg($msg, $ok = true) { global $log; $log[] = ['msg'=>$msg,'ok'=>$ok]; }

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=$charset", $user, $pass,
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    logMsg("✅ Connected to database.");

    // 1. Add role to users
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('student','owner','admin') NOT NULL DEFAULT 'student' AFTER phone");
        logMsg("✅ Added 'role' column to users.");
    } else { logMsg("ℹ️ 'role' column already exists."); }

    // 2. Add owner_id to properties
    $cols = $pdo->query("SHOW COLUMNS FROM properties LIKE 'owner_id'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN owner_id INT DEFAULT NULL AFTER id");
        logMsg("✅ Added 'owner_id' column to properties.");
    } else { logMsg("ℹ️ 'owner_id' already exists."); }

    // 3. Add total_rooms
    $cols = $pdo->query("SHOW COLUMNS FROM properties LIKE 'total_rooms'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN total_rooms INT DEFAULT 10 AFTER image");
        logMsg("✅ Added 'total_rooms' column.");
    } else { logMsg("ℹ️ 'total_rooms' already exists."); }

    // 4. Add available_rooms
    $cols = $pdo->query("SHOW COLUMNS FROM properties LIKE 'available_rooms'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN available_rooms INT DEFAULT 5 AFTER total_rooms");
        logMsg("✅ Added 'available_rooms' column.");
    } else { logMsg("ℹ️ 'available_rooms' already exists."); }

    // 5. Add status
    $cols = $pdo->query("SHOW COLUMNS FROM properties LIKE 'status'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER available_rooms");
        logMsg("✅ Added 'status' column to properties.");
    } else { logMsg("ℹ️ 'status' already exists."); }

    // 6. Add phone to properties (owner contact)
    $cols = $pdo->query("SHOW COLUMNS FROM properties LIKE 'contact_phone'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN contact_phone VARCHAR(15) DEFAULT NULL AFTER status");
        logMsg("✅ Added 'contact_phone' column.");
    } else { logMsg("ℹ️ 'contact_phone' already exists."); }

    // 7. Set all existing properties to approved
    $pdo->exec("UPDATE properties SET status='approved' WHERE status IS NULL OR status=''");
    logMsg("✅ Existing properties marked as approved.");

    // 8. Create admin user
    $adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE email='admin@pgfinder.com'")->fetchColumn();
    if (!$adminExists) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name,email,password,phone,role) VALUES (?,?,?,?,?)")
            ->execute(['PGFinder Admin','admin@pgfinder.com',$hash,'9000000000','admin']);
        logMsg("✅ Admin user created. Email: admin@pgfinder.com | Pass: admin123");
    } else {
        $pdo->exec("UPDATE users SET role='admin' WHERE email='admin@pgfinder.com'");
        logMsg("ℹ️ Admin user already exists.");
    }

    // 9. Create demo owner
    $ownerExists = $pdo->query("SELECT COUNT(*) FROM users WHERE email='owner@pgfinder.com'")->fetchColumn();
    if (!$ownerExists) {
        $hash = password_hash('owner123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name,email,password,phone,role) VALUES (?,?,?,?,?)")
            ->execute(['Demo Owner','owner@pgfinder.com',$hash,'9111111111','owner']);
        logMsg("✅ Demo owner created. Email: owner@pgfinder.com | Pass: owner123");
    } else { logMsg("ℹ️ Demo owner already exists."); }

    logMsg("🎉 Migration complete!");

} catch(Exception $e) {
    logMsg("❌ ERROR: ".$e->getMessage(), false);
    $success = false;
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>PGFinder Migration</title>
<style>
body{font-family:'Segoe UI',sans-serif;background:#0a0f1e;color:#f1f5f9;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
.card{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:2.5rem;max-width:580px;width:100%}
h1{font-size:1.5rem;color:#f5c842;margin-bottom:1.5rem}
.log{padding:.55rem .8rem;border-radius:8px;margin-bottom:8px;font-size:.88rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08)}
.fail{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:#fca5a5}
.btn{display:block;text-align:center;padding:.85rem;border-radius:10px;font-weight:700;text-decoration:none;margin-top:1.2rem;font-size:.95rem}
.go{background:linear-gradient(135deg,#e8b820,#f5c842);color:#0a0f1e}
.admin{background:rgba(79,142,247,.2);border:1px solid rgba(79,142,247,.4);color:#93c5fd}
</style></head><body>
<div class="card">
  <h1>🔧 PGFinder — Database Migration</h1>
  <?php foreach($log as $e): ?>
    <div class="log <?= $e['ok']?'':'fail' ?>"><?= htmlspecialchars($e['msg']) ?></div>
  <?php endforeach; ?>
  <?php if($success): ?>
    <a href="index.php"       class="btn go">🏠 Go to PGFinder &rarr;</a>
    <a href="admin/index.php" class="btn admin">🛡 Admin Panel &rarr;</a>
    <p style="font-size:.78rem;color:#64748b;text-align:center;margin-top:1rem">Delete migrate.php after running.</p>
  <?php else: ?>
    <a href="migrate.php" class="btn" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15)">🔄 Retry</a>
  <?php endif; ?>
</div></body></html>
