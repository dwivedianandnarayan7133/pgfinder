<?php
/**
 * index.php — Property Listing Page (Home)
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
  <title>PGFinder — Find Your Perfect Student Accommodation</title>
  <meta name="description" content="Discover verified PG accommodations for students across India. Filter by city, budget, and gender preference. Safe, affordable, and comfortable living.">

  <!-- Bootstrap 5 + Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="css/style.css">

  <style>
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<!-- ── Navbar ────────────────────────────────────────────── -->
<nav class="navbar-custom">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="navbar-brand-custom">
      <span class="brand-icon">🏠</span> PGFinder
    </a>

    <div class="d-flex align-items-center gap-2">
      <a href="index.php"       class="nav-link-custom active">Listings</a>
      <a href="shortlist.php"   class="nav-link-custom">
        <i class="bi bi-heart"></i> Shortlist
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

<!-- ── Hero ──────────────────────────────────────────────── -->
<section class="hero z-1">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <p class="section-label">🇮🇳 India's Trusted Student PG Platform</p>
        <h1 class="hero-title">Find Your Perfect<br>Student Home</h1>
        <div class="divider-gold"></div>
        <p class="hero-subtitle">
          Discover verified PG accommodations across major cities.
          Filter by budget, city, and your preferences — all in one place.
        </p>

        <!-- Search Bar -->
        <div class="search-bar-wrap mt-4">
          <i class="bi bi-search" style="color:var(--text-muted);margin-left:.8rem"></i>
          <input type="text" id="search-input" class="search-input"
                 placeholder="Search by city, area, or PG name…">
          <button class="btn-search" id="search-btn">
            <i class="bi bi-search"></i> Search
          </button>
        </div>

        <!-- Stats -->
        <div class="hero-stats">
          <div class="stat-item">
            <div class="stat-number">500+</div>
            <div class="stat-label">Properties</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">20+</div>
            <div class="stat-label">Cities</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">10K+</div>
            <div class="stat-label">Happy Students</div>
          </div>
        </div>
      </div>

      <div class="col-lg-5 d-none d-lg-flex justify-content-end">
        <div style="position:relative">
          <img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=600"
               alt="Comfortable student room"
               style="width:380px;height:320px;object-fit:cover;border-radius:var(--radius-xl);border:2px solid var(--glass-border);">
          <!-- Floating card -->
          <div style="position:absolute;bottom:-20px;left:-30px;background:var(--glass-bg);border:1px solid var(--glass-border);border-radius:var(--radius-md);padding:.9rem 1.2rem;backdrop-filter:blur(12px);white-space:nowrap;">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:2px">Average Price</div>
            <div style="font-size:1.3rem;font-weight:800;color:var(--gold-400)">₹8,500<span style="font-size:.75rem;font-weight:400;color:var(--text-muted)">/mo</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Main Content ───────────────────────────────────────── -->
<section class="z-1 pb-5">
  <div class="container">
    <div class="row">

      <!-- Filter Sidebar -->
      <div class="col-lg-3 mb-4">
        <div class="filter-panel">
          <div class="filter-title"><i class="bi bi-sliders2"></i> Filters</div>

          <!-- City -->
          <div class="filter-section">
            <h6>City</h6>
            <div class="filter-chip-group">
              <button class="filter-chip city-chip active" data-city="all">All</button>
              <button class="filter-chip city-chip" data-city="Bangalore">Bangalore</button>
              <button class="filter-chip city-chip" data-city="Pune">Pune</button>
              <button class="filter-chip city-chip" data-city="Hyderabad">Hyderabad</button>
              <button class="filter-chip city-chip" data-city="Chennai">Chennai</button>
              <button class="filter-chip city-chip" data-city="Delhi">Delhi</button>
            </div>
          </div>

          <!-- Gender -->
          <div class="filter-section">
            <h6>Gender</h6>
            <div class="filter-chip-group">
              <button class="filter-chip gender-chip" data-gender="male">
                <i class="bi bi-gender-male"></i> Boys
              </button>
              <button class="filter-chip gender-chip" data-gender="female">
                <i class="bi bi-gender-female"></i> Girls
              </button>
              <button class="filter-chip gender-chip" data-gender="any">
                Any
              </button>
            </div>
          </div>

          <!-- Budget -->
          <div class="filter-section">
            <h6>Max Budget</h6>
            <div class="price-range-label">
              <span>₹0</span>
              <span class="price-range-value" id="price-display">₹20,000</span>
            </div>
            <input type="range" id="price-slider" min="3000" max="20000"
                   step="500" value="20000">
          </div>

          <button class="btn-filter-apply" id="apply-filters">
            <i class="bi bi-funnel-fill"></i> Apply Filters
          </button>
          <button class="btn-filter-reset" id="reset-filters">
            Reset All
          </button>
        </div>
      </div>

      <!-- Property Grid -->
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <p class="section-label mb-0">Available Properties</p>
            <p class="result-count" id="result-count">Loading…</p>
          </div>
        </div>

        <div class="row" id="property-grid">
          <!-- Skeleton loading placeholders -->
          <?php for ($i = 0; $i < 6; $i++): ?>
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="skeleton-card skeleton-pulse" style="height:380px;border-radius:var(--radius-lg)"></div>
          </div>
          <?php endfor; ?>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ── Footer ─────────────────────────────────────────────── -->
<footer>
  <div class="container">
    <div class="row">
      <div class="col-md-4 mb-3">
        <div class="footer-brand mb-2">🏠 PGFinder</div>
        <p class="footer-text">India's trusted student accommodation platform. Find safe, verified, and affordable PGs near your college.</p>
      </div>
      <div class="col-md-4 mb-3">
        <h6 style="color:var(--text-secondary);font-size:.8rem;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:.8rem">Quick Links</h6>
        <div class="d-flex flex-column gap-1">
          <a href="index.php"    class="footer-text no-underline" style="transition:.2s" onmouseover="this.style.color='var(--gold-400)'" onmouseout="this.style.color=''">Browse Properties</a>
          <a href="shortlist.php" class="footer-text no-underline" style="transition:.2s" onmouseover="this.style.color='var(--gold-400)'" onmouseout="this.style.color=''">My Shortlist</a>
          <a href="login.php"    class="footer-text no-underline" style="transition:.2s" onmouseover="this.style.color='var(--gold-400)'" onmouseout="this.style.color=''">Login / Sign Up</a>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <h6 style="color:var(--text-secondary);font-size:.8rem;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:.8rem">Top Cities</h6>
        <p class="footer-text">Bangalore • Pune • Hyderabad<br>Chennai • Delhi • Mumbai</p>
      </div>
    </div>
    <div class="footer-copy">© <?= date('Y') ?> PGFinder. Built for students, by students.</div>
  </div>
</footer>

<!-- Scripts -->
<script>window._isLoggedIn = <?= $loggedIn ? 'true' : 'false' ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
<script src="js/filters.js"></script>

<?php include_once 'config/notification_modal.php'; ?>
</body>
</html>
