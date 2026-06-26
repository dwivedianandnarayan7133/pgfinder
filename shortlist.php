<?php
/**
 * shortlist.php — User Shortlist Page (React-powered)
 * Student Accommodation Platform
 */
require_once 'config/db.php';
startSession();

$loggedIn = isLoggedIn();
$userName = $loggedIn ? htmlspecialchars($_SESSION['user_name'] ?? 'Student') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Shortlist — PGFinder</title>
  <meta name="description" content="View and manage your shortlisted PG properties on PGFinder.">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">

  <style>
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-custom">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="navbar-brand-custom"><span class="brand-icon">🏠</span> PGFinder</a>
    <div class="d-flex align-items-center gap-2">
      <a href="index.php"       class="nav-link-custom">Listings</a>
      <a href="shortlist.php"   class="nav-link-custom active">
        <i class="bi bi-heart-fill" style="color:var(--gold-400)"></i> Shortlist
      </a>
      <a href="owner-login.php" class="nav-link-custom">
        <i class="bi bi-building"></i> List Your PG
      </a>
      <?php if ($loggedIn): ?>
        <?php if (isAdmin()): ?>
          <a href="admin/index.php" class="nav-link-custom" style="color:var(--accent-blue)">
            <i class="bi bi-shield-check"></i> Admin Panel
          </a>
        <?php elseif (isOwner()): ?>
          <a href="owner-dashboard.php" class="nav-link-custom" style="color:var(--gold-400)">
            <i class="bi bi-building"></i> My Properties
          </a>
        <?php endif; ?>
        <span class="nav-link-custom" style="color:var(--gold-400)">
          <i class="bi bi-person-circle"></i> <?= $userName ?>
        </span>
        <a href="logout.php" class="btn-nav-login">Logout</a>
      <?php else: ?>
        <a href="admin/login.php" class="nav-link-custom">
          <i class="bi bi-shield-lock"></i> Admin
        </a>
        <a href="login.php"  class="btn-nav-login">Login</a>
        <a href="signup.php" class="btn-nav-cta">Sign Up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Page Header -->
<div class="z-1" style="padding:3rem 0 1rem">
  <div class="container">
    <p class="section-label">❤ Saved Properties</p>
    <h1 class="section-title">My Shortlist</h1>
    <div class="divider-gold"></div>
    <?php if (!$loggedIn): ?>
    <p style="color:var(--text-secondary)">
      <i class="bi bi-lock"></i>
      Please <a href="login.php" class="auth-link">login</a> to view your shortlisted properties.
    </p>
    <?php else: ?>
    <p style="color:var(--text-secondary);font-size:.9rem">
      Your shortlisted PG properties — manage them here.
    </p>
    <?php endif; ?>
  </div>
</div>

<!-- React Root -->
<div class="shortlist-page z-1">
  <div class="container">
    <?php if (!$loggedIn): ?>
    <!-- Not logged in — show prompt -->
    <div class="shortlist-empty">
      <div class="empty-icon">🔐</div>
      <h4 style="color:var(--text-secondary);margin-bottom:.5rem">Login Required</h4>
      <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:1.5rem">
        Create an account or login to save and manage your favourite PGs.
      </p>
      <div class="d-flex gap-2 justify-content-center">
        <a href="login.php"  class="btn-auth" style="display:inline-block;padding:.75rem 2rem;text-decoration:none;width:auto">Login</a>
        <a href="signup.php" style="display:inline-block;padding:.75rem 2rem;text-decoration:none;width:auto;background:transparent;border:1px solid var(--glass-border);color:var(--text-primary);border-radius:var(--radius-md);font-weight:600;text-align:center">Sign Up</a>
      </div>
    </div>
    <?php else: ?>
    <!-- React mounts here -->
    <div id="react-shortlist-root"></div>
    <?php endif; ?>
  </div>
</div>

<!-- Footer -->
<footer>
  <div class="container">
    <div class="footer-copy">© <?= date('Y') ?> PGFinder. Find your perfect student home.</div>
  </div>
</footer>

<!-- React 18 CDN -->
<script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
<?php if ($loggedIn): ?>
<script src="js/react-shortlist.js"></script>
<?php endif; ?>

</body>
</html>
