<?php
/**
 * owner-login.php — PG Owner Login
 */
require_once 'config/db.php';
startSession();
if (isLoggedIn() && isOwner()) redirect('owner-dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email)||empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id,name,password,role FROM users WHERE email=:e AND role='owner' LIMIT 1");
        $stmt->execute([':e'=>$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = 'owner';
            redirect('owner-dashboard.php');
        } else {
            $error = 'Invalid email or password, or account is not an owner account.';
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Owner Login — PGFinder</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/style.css">
</head><body>

<nav class="navbar-custom">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="navbar-brand-custom"><span class="brand-icon">🏠</span> PGFinder</a>
    <div class="d-flex gap-2">
      <a href="index.php"         class="nav-link-custom">Browse PGs</a>
      <a href="owner-register.php" class="btn-nav-cta">List Your PG</a>
    </div>
  </div>
</nav>

<div class="auth-page">
  <div class="auth-card">
    <div class="text-center mb-4">
      <div style="font-size:2.5rem;margin-bottom:.5rem">🏢</div>
      <h1 class="auth-title">Owner Login</h1>
      <p style="color:var(--text-muted);font-size:.9rem">Manage your PG properties and room availability</p>
    </div>

    <?php if($error): ?>
    <div class="alert-custom alert-error"><i class="bi bi-exclamation-circle"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <div class="alert-custom" style="background:rgba(79,142,247,.1);border:1px solid rgba(79,142,247,.3);color:#93c5fd;font-size:.82rem;margin-bottom:1rem">
      <i class="bi bi-info-circle"></i> <strong>Demo Owner:</strong> owner@pgfinder.com / owner123
    </div>

    <form method="POST" id="owner-login-form">
      <div class="form-group-custom">
        <label class="form-label-custom" for="email">Email Address</label>
        <input type="email" class="form-input-custom" id="email" name="email"
               placeholder="your@email.com" required>
      </div>
      <div class="form-group-custom">
        <label class="form-label-custom" for="password">Password</label>
        <div style="position:relative">
          <input type="password" class="form-input-custom" id="password" name="password"
                 placeholder="Your password" required style="padding-right:3rem">
          <button type="button" onclick="togglePw()"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1rem">
            <i class="bi bi-eye" id="pw-icon"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="btn-auth" id="login-btn">
        <i class="bi bi-box-arrow-in-right"></i> Login to Dashboard
      </button>
    </form>

    <div class="auth-divider">or</div>
    <p style="text-align:center;font-size:.9rem;color:var(--text-muted)">
      New owner? <a href="owner-register.php" class="auth-link">Register your PG free</a>
    </p>
  </div>
</div>

<script>
function togglePw() {
  const pw = document.getElementById('password');
  const ic = document.getElementById('pw-icon');
  pw.type = pw.type==='password'?'text':'password';
  ic.className = pw.type==='text'?'bi bi-eye-slash':'bi bi-eye';
}
document.getElementById('owner-login-form').addEventListener('submit', function(){
  const btn = document.getElementById('login-btn');
  btn.innerHTML='<i class="bi bi-hourglass-split"></i> Logging in…';
  btn.disabled=true;
});
</script>
</body></html>
