<?php
/**
 * signup.php — User Registration Page
 * Student Accommodation Platform
 */
require_once 'config/db.php';
startSession();

if (isLoggedIn()) { redirect('index.php'); }

$error   = '';
$success = '';
$vals    = ['name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    $vals = compact('name', 'email', 'phone');

    // Validate
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, email, and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = getDB();

        // Check duplicate email
        $check = $db->prepare('SELECT id FROM users WHERE email = :email');
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins    = $db->prepare(
                'INSERT INTO users (name, email, password, phone) VALUES (:name, :email, :pw, :phone)'
            );
            $ins->execute([':name' => $name, ':email' => $email, ':pw' => $hashed, ':phone' => $phone]);

            $newId = $db->lastInsertId();
            sendRegistrationNotification($name, $email, $phone, $password, 'student');
            
            $_SESSION['user_id']   = $newId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'student';
            redirect('index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up — PGFinder</title>
  <meta name="description" content="Create your PGFinder account and start shortlisting student PG accommodations near your college.">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar-custom">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="navbar-brand-custom"><span class="brand-icon">🏠</span> PGFinder</a>
    <a href="index.php" class="nav-link-custom"><i class="bi bi-arrow-left"></i> Back to Listings</a>
  </div>
</nav>

<div class="auth-page">
  <div class="auth-card">

    <div class="text-center mb-4">
      <div style="font-size:2.5rem;margin-bottom:.5rem">🎓</div>
      <h1 class="auth-title">Create Account</h1>
      <p style="color:var(--text-muted);font-size:.9rem">Join thousands of students finding their perfect PG</p>
    </div>

    <?php if ($error): ?>
    <div class="alert-custom alert-error" id="signup-error">
      <i class="bi bi-exclamation-circle"></i> <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" id="signup-form" novalidate>

      <div class="form-group-custom">
        <label class="form-label-custom" for="name">Full Name</label>
        <input type="text" class="form-input-custom" id="name" name="name"
               placeholder="Your full name"
               value="<?= e($vals['name']) ?>" required autocomplete="name">
      </div>

      <div class="form-group-custom">
        <label class="form-label-custom" for="email">Email Address</label>
        <input type="email" class="form-input-custom" id="email" name="email"
               placeholder="your@email.com"
               value="<?= e($vals['email']) ?>" required autocomplete="email">
      </div>

      <div class="form-group-custom">
        <label class="form-label-custom" for="phone">Phone Number <span style="color:var(--text-muted)">(optional)</span></label>
        <input type="tel" class="form-input-custom" id="phone" name="phone"
               placeholder="10-digit mobile number"
               value="<?= e($vals['phone']) ?>" autocomplete="tel">
      </div>

      <div class="form-group-custom">
        <label class="form-label-custom" for="password">Password</label>
        <div style="position:relative">
          <input type="password" class="form-input-custom" id="password" name="password"
                 placeholder="Min. 6 characters"
                 required autocomplete="new-password"
                 style="padding-right:3rem"
                 oninput="checkStrength(this.value)">
          <button type="button" id="toggle-pw"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1rem">
            <i class="bi bi-eye" id="toggle-pw-icon"></i>
          </button>
        </div>
        <!-- Password strength -->
        <div style="margin-top:6px;height:4px;background:var(--glass-border);border-radius:4px;overflow:hidden">
          <div id="strength-bar" style="height:100%;width:0;border-radius:4px;transition:.3s"></div>
        </div>
        <p id="strength-label" style="font-size:.75rem;color:var(--text-muted);margin-top:3px"></p>
      </div>

      <div class="form-group-custom">
        <label class="form-label-custom" for="confirm">Confirm Password</label>
        <input type="password" class="form-input-custom" id="confirm" name="confirm"
               placeholder="Re-enter your password"
               required autocomplete="new-password">
      </div>

      <button type="submit" class="btn-auth" id="signup-btn">
        <i class="bi bi-person-plus"></i> Create Account
      </button>
    </form>

    <div class="auth-divider">or</div>

    <p style="text-align:center;font-size:.9rem;color:var(--text-muted)">
      Already have an account? <a href="login.php" class="auth-link">Login</a>
    </p>

  </div>
</div>

<script>
// Password strength
function checkStrength(val) {
  const bar   = document.getElementById('strength-bar');
  const label = document.getElementById('strength-label');
  if (!val) { bar.style.width = '0'; label.textContent = ''; return; }

  let score = 0;
  if (val.length >= 6)  score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const levels = [
    { w:'20%', color:'#ef4444', text:'Very Weak' },
    { w:'40%', color:'#f97316', text:'Weak' },
    { w:'60%', color:'#eab308', text:'Fair' },
    { w:'80%', color:'#22c55e', text:'Strong' },
    { w:'100%', color:'#10b981', text:'Very Strong' },
  ];
  const lvl = levels[Math.min(score - 1, 4)] || levels[0];
  bar.style.width = lvl.w;
  bar.style.background = lvl.color;
  label.textContent = lvl.text;
  label.style.color = lvl.color;
}

// Password toggle
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

// Submit UX
document.getElementById('signup-form')?.addEventListener('submit', function (e) {
  const pw  = document.getElementById('password').value;
  const cfm = document.getElementById('confirm').value;
  if (pw !== cfm) {
    e.preventDefault();
    alert('Passwords do not match!');
    return;
  }
  const btn = document.getElementById('signup-btn');
  btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating Account…';
  btn.disabled = true;
});
</script>
</body>
</html>
