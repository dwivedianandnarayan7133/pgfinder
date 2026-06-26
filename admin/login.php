<?php
/**
 * admin/login.php — Admin Login
 */
require_once __DIR__ . '/../config/db.php';
startSession();
if (isset($_SESSION['admin_id'])) { redirect(BASE_URL . 'admin/index.php'); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email)||empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id,name,password FROM users WHERE email=:e AND role='admin' LIMIT 1");
        $stmt->execute([':e'=>$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id']   = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_role']  = 'admin';
            redirect(BASE_URL . 'admin/index.php');
        } else {
            $error = 'Invalid admin credentials.';
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — PGFinder</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../css/style.css">
<style>
.admin-login-badge {
  background:linear-gradient(135deg,rgba(79,142,247,.2),rgba(45,212,191,.15));
  border:1px solid rgba(79,142,247,.3);
  border-radius:10px;padding:.8rem 1rem;
  font-size:.83rem;color:#93c5fd;margin-bottom:1.2rem;
  display:flex;align-items:center;gap:8px;
}
</style>
</head><body>

<nav class="navbar-custom">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="../index.php" class="navbar-brand-custom"><span class="brand-icon">🏠</span> PGFinder</a>
    <a href="../index.php" class="nav-link-custom"><i class="bi bi-arrow-left"></i> Back to Site</a>
  </div>
</nav>

<div class="auth-page">
  <div class="auth-card">
    <div class="text-center mb-4">
      <div style="font-size:2.5rem;margin-bottom:.5rem">🛡️</div>
      <h1 class="auth-title">Admin Panel</h1>
      <p style="color:var(--text-muted);font-size:.9rem">Restricted access — authorized personnel only</p>
    </div>

    <div class="admin-login-badge">
      <i class="bi bi-shield-lock-fill"></i>
      <span><strong>Demo Admin:</strong> admin@pgfinder.com / admin123</span>
    </div>

    <?php if($error): ?>
    <div class="alert-custom alert-error"><i class="bi bi-exclamation-circle"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="admin-login-form">
      <div class="form-group-custom">
        <label class="form-label-custom" for="email">Admin Email</label>
        <input type="email" class="form-input-custom" id="email" name="email"
               placeholder="admin@pgfinder.com" required>
      </div>
      <div class="form-group-custom">
        <label class="form-label-custom" for="password">Password</label>
        <input type="password" class="form-input-custom" id="password" name="password"
               placeholder="Admin password" required>
      </div>
      <button type="submit" class="btn-auth" id="admin-login-btn"
              style="background:linear-gradient(135deg,#4f8ef7,#2dd4bf)">
        <i class="bi bi-shield-check"></i> Access Admin Panel
      </button>
    </form>
  </div>
</div>

<script>
document.getElementById('admin-login-form').addEventListener('submit', function(){
  const btn = document.getElementById('admin-login-btn');
  btn.innerHTML='<i class="bi bi-hourglass-split"></i> Verifying…';
  btn.disabled=true;
});
</script>
</body></html>
