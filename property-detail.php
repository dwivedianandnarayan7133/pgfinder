<?php
/**
 * property-detail.php — Single Property Detail Page
 * Student Accommodation Platform
 */
require_once 'config/db.php';
startSession();

$loggedIn = isLoggedIn();
$userId   = currentUserId();
$userName = $loggedIn ? htmlspecialchars($_SESSION['user_name'] ?? 'Student') : '';

// Validate property ID
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { redirect('index.php'); }

$db = getDB();

// Fetch property
$stmt = $db->prepare('SELECT * FROM properties WHERE id = :id');
$stmt->execute([':id' => $id]);
$property = $stmt->fetch();

if (!$property) { redirect('index.php'); }

// Fetch amenities
$aStmt = $db->prepare('
    SELECT a.name, a.icon
    FROM amenities a
    JOIN property_amenities pa ON pa.amenity_id = a.id
    WHERE pa.property_id = :id
');
$aStmt->execute([':id' => $id]);
$amenities = $aStmt->fetchAll();

// Check if current user is interested
$isInterested = false;
if ($loggedIn) {
    $iStmt = $db->prepare(
        'SELECT 1 FROM interested_users WHERE user_id = :uid AND property_id = :pid'
    );
    $iStmt->execute([':uid' => $userId, ':pid' => $id]);
    $isInterested = (bool) $iStmt->fetch();
}

// Related properties (same city, exclude current)
$rStmt = $db->prepare('
    SELECT id, name, price, rating, gender, image
    FROM properties
    WHERE city = :city AND id != :id
    LIMIT 3
');
$rStmt->execute([':city' => $property['city'], ':id' => $id]);
$related = $rStmt->fetchAll();

// Stars
function stars(float $r): string {
    $full  = floor($r);
    $half  = ($r - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    return str_repeat('<i class="bi bi-star-fill"></i>', $full)
         . ($half ? '<i class="bi bi-star-half"></i>' : '')
         . str_repeat('<i class="bi bi-star"></i>', $empty);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($property['name']) ?> — PGFinder</title>
  <meta name="description" content="<?= e(substr($property['description'] ?? '', 0, 155)) ?>">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar-custom">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="navbar-brand-custom"><span class="brand-icon">🏠</span> PGFinder</a>
    <div class="d-flex align-items-center gap-2">
      <a href="index.php"       class="nav-link-custom">Listings</a>
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

<!-- Breadcrumb -->
<div class="z-1" style="padding:1rem 0 0">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb" style="background:none;padding:0;font-size:.85rem">
        <li class="breadcrumb-item"><a href="index.php" style="color:var(--gold-400);text-decoration:none">Home</a></li>
        <li class="breadcrumb-item" style="color:var(--text-muted)"><?= e($property['city']) ?></li>
        <li class="breadcrumb-item active" style="color:var(--text-secondary)" aria-current="page"><?= e($property['name']) ?></li>
      </ol>
    </nav>
  </div>
</div>

<!-- Main -->
<main class="z-1 py-4">
  <div class="container">
    <div class="row g-4">

      <!-- Left: Images + Details -->
      <div class="col-lg-8">

        <!-- Main Image -->
        <img src="<?= e($property['image']) ?>"
             alt="<?= e($property['name']) ?>"
             class="detail-hero-img mb-4"
             onerror="this.src='https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800'">

        <!-- Info Card -->
        <div class="detail-card mb-4">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
              <h1 style="font-size:1.7rem;font-weight:800;margin-bottom:.3rem"><?= e($property['name']) ?></h1>
              <p style="color:var(--text-muted);font-size:.9rem;margin:0">
                <i class="bi bi-geo-alt-fill" style="color:var(--gold-400)"></i>
                <?= e($property['address']) ?>
              </p>
            </div>
            <div class="text-end">
              <?php
                $gClass = ['male'=>'badge-male','female'=>'badge-female','any'=>'badge-any'];
                $gLabel = ['male'=>'♂ Boys','female'=>'♀ Girls','any'=>'⚥ Any'];
                $gen    = $property['gender'];
              ?>
              <span class="card-gender-badge <?= $gClass[$gen] ?>" style="position:static;display:inline-block">
                <?= $gLabel[$gen] ?>
              </span>
            </div>
          </div>

          <!-- Rating Row -->
          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="rating-stars"><?= stars((float)$property['rating']) ?></div>
            <span style="font-weight:700;color:var(--text-primary)"><?= number_format($property['rating'],1) ?></span>
            <span style="color:var(--text-muted);font-size:.85rem">/ 5.0</span>
          </div>

          <!-- Description -->
          <p style="color:var(--text-secondary);line-height:1.8;font-size:.95rem">
            <?= e($property['description'] ?? 'No description available.') ?>
          </p>
        </div>

        <!-- Amenities -->
        <div class="detail-card">
          <h2 style="font-size:1.15rem;font-weight:700;margin-bottom:1.2rem">
            <i class="bi bi-grid-3x3-gap" style="color:var(--gold-400)"></i>
            Amenities & Features
          </h2>
          <?php if (empty($amenities)): ?>
            <p style="color:var(--text-muted);font-size:.9rem">No amenities listed.</p>
          <?php else: ?>
            <div class="row row-cols-2 row-cols-md-3 g-2">
              <?php foreach ($amenities as $am): ?>
              <div class="col">
                <div class="amenity-item">
                  <i class="bi <?= e($am['icon']) ?>"></i>
                  <?= e($am['name']) ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>

      <!-- Right: Pricing + CTA -->
      <div class="col-lg-4">
        <div class="detail-card" style="position:sticky;top:90px">

          <!-- Price -->
          <div class="mb-3">
            <p style="font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--text-muted);margin-bottom:4px">Monthly Rent</p>
            <div class="price-display">
              <?= '₹' . number_format($property['price'], 0) ?>
              <div class="per-month">per month</div>
            </div>
          </div>

          <hr style="border-color:var(--glass-border);margin:1rem 0">

          <!-- Quick info -->
          <div class="d-flex flex-column gap-2 mb-3">
            <div class="d-flex align-items-center gap-2" style="font-size:.9rem;color:var(--text-secondary)">
              <i class="bi bi-geo-alt" style="color:var(--gold-400)"></i>
              <span><?= e($property['city']) ?></span>
            </div>
            <div class="d-flex align-items-center gap-2" style="font-size:.9rem;color:var(--text-secondary)">
              <i class="bi bi-people" style="color:var(--gold-400)"></i>
              <span><?= ucfirst($property['gender']) ?> PG</span>
            </div>
            <div class="d-flex align-items-center gap-2" style="font-size:.9rem;color:var(--text-secondary)">
              <i class="bi bi-star" style="color:var(--gold-400)"></i>
              <span>Rated <?= number_format($property['rating'],1) ?> / 5</span>
            </div>
          </div>

          <hr style="border-color:var(--glass-border);margin:1rem 0">

          <!-- Interest Button -->
          <?php if ($loggedIn): ?>
          <button id="interest-btn"
                  class="btn-interest <?= $isInterested ? 'interested' : '' ?>"
                  data-property-id="<?= $id ?>"
                  onclick="handleDetailInterest(this)">
            <i class="bi <?= $isInterested ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
            <?= $isInterested ? 'Shortlisted!' : 'Mark as Interested' ?>
          </button>
          <?php else: ?>
          <a href="login.php?redirect=property-detail.php?id=<?= $id ?>" class="btn-interest" style="text-decoration:none;text-align:center">
            <i class="bi bi-heart"></i> Login to Shortlist
          </a>
          <?php endif; ?>

          <a href="index.php" class="btn-view-details mt-2" style="text-align:center">
            <i class="bi bi-arrow-left"></i> Back to Listings
          </a>

        </div>
      </div>

    </div>

    <!-- Related Properties -->
    <?php if (!empty($related)): ?>
    <div class="mt-5">
      <p class="section-label">More in <?= e($property['city']) ?></p>
      <h2 class="section-title mb-4">Similar Properties</h2>
      <div class="row">
        <?php foreach ($related as $r): ?>
        <div class="col-md-4 mb-4">
          <div class="property-card" onclick="window.location.href='property-detail.php?id=<?= $r['id'] ?>'">
            <div class="card-img-wrap">
              <img src="<?= e($r['image']) ?>" alt="<?= e($r['name']) ?>"
                   loading="lazy"
                   onerror="this.src='https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800'">
              <?php
                $gClass2 = ['male'=>'badge-male','female'=>'badge-female','any'=>'badge-any'];
                $gLabel2 = ['male'=>'♂ Boys','female'=>'♀ Girls','any'=>'⚥ Any'];
              ?>
              <span class="card-gender-badge <?= $gClass2[$r['gender']] ?>"><?= $gLabel2[$r['gender']] ?></span>
            </div>
            <div class="card-body-custom">
              <div class="card-property-name"><?= e($r['name']) ?></div>
              <div class="card-footer-custom mt-2">
                <div class="card-price"><?= '₹' . number_format($r['price'],0) ?><span>/mo</span></div>
                <div class="card-rating"><i class="bi bi-star-fill"></i> <?= number_format($r['rating'],1) ?></div>
              </div>
              <a href="property-detail.php?id=<?= $r['id'] ?>" class="btn-view-details" onclick="event.stopPropagation()">
                View Details <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</main>

<!-- Footer -->
<footer>
  <div class="container">
    <div class="footer-copy">© <?= date('Y') ?> PGFinder. Find your perfect student home.</div>
  </div>
</footer>

<script>window._isLoggedIn = <?= $loggedIn ? 'true' : 'false' ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
<script>
function handleDetailInterest(btn) {
  const pid = btn.dataset.propertyId;
  if (!window._isLoggedIn) {
    showToast('Please login to shortlist properties!', 'info');
    setTimeout(() => window.location.href = 'login.php', 1200);
    return;
  }

  btn.disabled = true;
  const fd = new FormData();
  fd.append('property_id', pid);

  fetch('api/toggle_interest.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const isInt = data.is_interested;
        btn.classList.toggle('interested', isInt);
        btn.innerHTML = isInt
          ? '<i class="bi bi-heart-fill"></i> Shortlisted!'
          : '<i class="bi bi-heart"></i> Mark as Interested';
        showToast(data.message, isInt ? 'success' : 'info');
      } else {
        showToast(data.message, 'error');
      }
    })
    .catch(() => showToast('Network error!', 'error'))
    .finally(() => btn.disabled = false);
}
</script>
</body>
</html>
