<?php
/**
 * login.php — User Login Page
 * Student Accommodation Platform
 */
require_once 'config/db.php';
startSession();

if (isLoggedIn()) { redirect('index.php'); }

$error    = '';
$redirect = htmlspecialchars($_GET['redirect'] ?? 'index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT id, name, password, role FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            // Role-based redirect
            if ($user['role'] === 'admin') {
                $_SESSION['admin_id']   = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                redirect('admin/index.php');
            } elseif ($user['role'] === 'owner') {
                redirect('owner-dashboard.php');
            } else {
                redirect($redirect);
            }
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — PGFinder</title>
  <meta name="description" content="Login to PGFinder to save and shortlist your favourite student accommodations.">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar-custom">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="navbar-brand-custom"><span class="brand-icon">🏠</span> PGFinder</a>
    <a href="index.php" class="nav-link-custom"><i class="bi bi-arrow-left"></i> Back to Listings</a>
  </div>
</nav>

<div class="auth-page">
  <div class="auth-card">

    <!-- Brand -->
    <div class="text-center mb-4">
      <div style="font-size:2.5rem;margin-bottom:.5rem">🔐</div>
      <h1 class="auth-title">Welcome Back</h1>
      <p style="color:var(--text-muted);font-size:.9rem">Login to access your shortlist and more</p>
    </div>

    <!-- Error -->
    <?php if ($error): ?>
    <div class="alert-custom alert-error" role="alert" id="login-error">
      <i class="bi bi-exclamation-circle"></i> <?= e($error) ?>
    </div>
    <?php endif; ?>

    <!-- Demo hint -->
    <div class="alert-custom" style="background:rgba(79,142,247,0.1);border:1px solid rgba(79,142,247,0.3);color:#93c5fd;font-size:.82rem;margin-bottom:1rem">
      <i class="bi bi-info-circle"></i>
      <strong>Demo:</strong> demo@pgfinder.com / demo1234
    </div>

    <form method="POST" id="login-form" novalidate>
      <input type="hidden" name="redirect" value="<?= $redirect ?>">

      <div class="form-group-custom">
        <label class="form-label-custom" for="email">Email Address</label>
        <input type="email"
               class="form-input-custom"
               id="email"
               name="email"
               placeholder="your@email.com"
               value="<?= e($_POST['email'] ?? '') ?>"
               required autocomplete="email">
      </div>

      <div class="form-group-custom">
        <label class="form-label-custom" for="password">Password</label>
        <div style="position:relative">
          <input type="password"
                 class="form-input-custom"
                 id="password"
                 name="password"
                 placeholder="Enter your password"
                 required autocomplete="current-password"
                 style="padding-right:3rem">
          <button type="button" id="toggle-pw"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1rem">
            <i class="bi bi-eye" id="toggle-pw-icon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-auth" id="login-btn">
        <i class="bi bi-box-arrow-in-right"></i> Login
      </button>
    </form>

    <div class="auth-divider">or</div>

    <p style="text-align:center;font-size:.9rem;color:var(--text-muted)">
      Don't have an account?
      <a href="signup.php" class="auth-link">Sign up free</a>
    </p>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle password visibility
const toggleBtn  = document.getElementById('toggle-pw');
const pwInput    = document.getElementById('password');
const toggleIcon = document.getElementById('toggle-pw-icon');

if (toggleBtn) {
  toggleBtn.addEventListener('click', () => {
    const isText = pwInput.type === 'text';
    pwInput.type = isText ? 'password' : 'text';
    toggleIcon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
  });
}

// Submit UX feedback
document.getElementById('login-form')?.addEventListener('submit', function () {
  const btn = document.getElementById('login-btn');
  btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Logging in…';
  btn.disabled = true;
});
</script>
</body>
</html>
