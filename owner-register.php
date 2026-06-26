<?php
/**
 * owner-register.php — PG Owner Registration
 */
require_once 'config/db.php';
startSession();
if (isLoggedIn()) {
    if (isOwner() || isAdmin()) {
        redirect('owner-dashboard.php');
    } else {
        redirect('index.php');
    }
}

$error = ''; $vals = ['name'=>'','email'=>'','phone'=>'','hostel_name'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']        ?? '');
    $email       = trim($_POST['email']       ?? '');
    $phone       = trim($_POST['phone']       ?? '');
    $password    = trim($_POST['password']    ?? '');
    $confirm     = trim($_POST['confirm']     ?? '');
    $vals = compact('name','email','phone');

    if (empty($name)||empty($email)||empty($password))
        $error = 'Name, email, and password are required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $error = 'Invalid email address.';
    elseif (strlen($password) < 6)
        $error = 'Password must be at least 6 characters.';
    elseif ($password !== $confirm)
        $error = 'Passwords do not match.';
    else {
        $db = getDB();
        $check = $db->prepare('SELECT id FROM users WHERE email=:e');
        $check->execute([':e'=>$email]);
        if ($check->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $db->prepare('INSERT INTO users (name,email,password,phone,role) VALUES (:n,:e,:p,:ph,:r)');
            $ins->execute([':n'=>$name,':e'=>$email,':p'=>$hash,':ph'=>$phone,':r'=>'owner']);
            $newId = $db->lastInsertId();
            sendRegistrationNotification($name, $email, $phone, $password, 'owner');
            
            $_SESSION['user_id']   = $newId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'owner';
            redirect('owner-dashboard.php');
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>List Your PG — PGFinder</title>
<meta name="description" content="Register your PG property on PGFinder and reach thousands of students.">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/style.css">
</head><body>

<nav class="navbar-custom">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="navbar-brand-custom"><span class="brand-icon">🏠</span> PGFinder</a>
    <div class="d-flex gap-2">
      <a href="owner-login.php" class="btn-nav-login">Owner Login</a>
      <a href="index.php"       class="nav-link-custom">Browse PGs</a>
    </div>
  </div>
</nav>

<div class="auth-page" style="padding:3rem 1rem">
  <div class="auth-card" style="max-width:500px">
    <div class="text-center mb-4">
      <div style="font-size:2.5rem;margin-bottom:.5rem">🏢</div>
      <h1 class="auth-title">List Your PG</h1>
      <p style="color:var(--text-muted);font-size:.9rem">Create an owner account to manage your properties</p>
    </div>

    <?php if($error): ?>
    <div class="alert-custom alert-error"><i class="bi bi-exclamation-circle"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="form-group-custom">
        <label class="form-label-custom" for="name">Your Full Name</label>
        <input type="text" class="form-input-custom" id="name" name="name"
               placeholder="Owner / Manager Name" value="<?= e($vals['name']) ?>" required>
      </div>
      <div class="form-group-custom">
        <label class="form-label-custom" for="email">Email Address</label>
        <input type="email" class="form-input-custom" id="email" name="email"
               placeholder="your@email.com" value="<?= e($vals['email']) ?>" required>
      </div>
      <div class="form-group-custom">
        <label class="form-label-custom" for="phone">Contact Number</label>
        <input type="tel" class="form-input-custom" id="phone" name="phone"
               placeholder="10-digit mobile" value="<?= e($vals['phone']) ?>">
      </div>
      <div class="form-group-custom">
        <label class="form-label-custom" for="password">Password</label>
        <input type="password" class="form-input-custom" id="password" name="password"
               placeholder="Min. 6 characters" required>
      </div>
      <div class="form-group-custom">
        <label class="form-label-custom" for="confirm">Confirm Password</label>
        <input type="password" class="form-input-custom" id="confirm" name="confirm"
               placeholder="Re-enter password" required>
      </div>

      <!-- Benefits -->
      <div style="background:rgba(79,142,247,.08);border:1px solid rgba(79,142,247,.2);border-radius:10px;padding:1rem;margin-bottom:1rem;font-size:.83rem;color:var(--text-secondary)">
        <div style="font-weight:600;color:var(--accent-blue);margin-bottom:.5rem"><i class="bi bi-stars"></i> Owner Benefits</div>
        <div>✅ List unlimited properties &nbsp;&nbsp; ✅ Manage room availability</div>
        <div>✅ Reach 10,000+ students &nbsp;&nbsp;&nbsp;&nbsp; ✅ Free registration</div>
      </div>

      <button type="submit" class="btn-auth">
        <i class="bi bi-building-add"></i> Create Owner Account
      </button>
    </form>

    <div class="auth-divider">or</div>
    <p style="text-align:center;font-size:.9rem;color:var(--text-muted)">
      Already registered? <a href="owner-login.php" class="auth-link">Login here</a>
    </p>
    <p style="text-align:center;font-size:.85rem;color:var(--text-muted);margin-top:.5rem">
      Looking for a PG? <a href="signup.php" class="auth-link">Student signup</a>
    </p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
