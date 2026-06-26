<?php
/**
 * owner-dashboard.php — PG Owner Dashboard
 * Shows owner's properties with availability management
 */
require_once 'config/db.php';
requireOwner();

$userId   = currentUserId();
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Owner');
$db       = getDB();

// Fetch owner's properties
$stmt = $db->prepare("
    SELECT p.*,
           COUNT(DISTINCT pa.amenity_id)  AS amenity_count,
           COUNT(DISTINCT iu.id)          AS interest_count
    FROM properties p
    LEFT JOIN property_amenities pa ON pa.property_id = p.id
    LEFT JOIN interested_users   iu ON iu.property_id  = p.id
    WHERE p.owner_id = :uid
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->execute([':uid' => $userId]);
$properties = $stmt->fetchAll();

// Stats
$totalProps     = count($properties);
$totalInterests = array_sum(array_column($properties, 'interest_count'));
$totalRooms     = array_sum(array_column($properties, 'total_rooms'));
$availRooms     = array_sum(array_column($properties, 'available_rooms'));
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Owner Dashboard — PGFinder</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/owner.css">
</head><body>

<!-- Owner Navbar -->
<nav class="navbar-custom">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
    <a href="index.php" class="navbar-brand-custom"><span class="brand-icon">🏠</span> PGFinder</a>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <span class="owner-badge"><i class="bi bi-building"></i> Owner Portal</span>
      <a href="owner-add-property.php" class="btn-nav-cta">
        <i class="bi bi-plus-circle"></i> Add Property
      </a>
      <span class="nav-link-custom" style="color:var(--gold-400)">
        <i class="bi bi-person-circle"></i> <?= $userName ?>
      </span>
      <a href="logout.php" class="btn-nav-login">Logout</a>
    </div>
  </div>
</nav>

<div class="owner-page">
  <div class="container">

    <!-- Header -->
    <div class="owner-header">
      <div>
        <p class="section-label">🏢 Owner Portal</p>
        <h1 class="section-title">My Dashboard</h1>
        <div class="divider-gold"></div>
      </div>
      <a href="owner-add-property.php" class="btn-auth" style="width:auto;padding:.75rem 1.8rem;display:inline-flex;align-items:center;gap:8px;text-decoration:none">
        <i class="bi bi-plus-circle-fill"></i> Add New Property
      </a>
    </div>

    <!-- Stats Cards -->
    <div class="owner-stats">
      <div class="owner-stat-card">
        <div class="stat-icon" style="background:rgba(245,200,66,.15);color:var(--gold-400)">
          <i class="bi bi-building"></i>
        </div>
        <div class="stat-info">
          <div class="stat-val"><?= $totalProps ?></div>
          <div class="stat-lbl">My Properties</div>
        </div>
      </div>
      <div class="owner-stat-card">
        <div class="stat-icon" style="background:rgba(45,212,191,.15);color:var(--accent-teal)">
          <i class="bi bi-door-open"></i>
        </div>
        <div class="stat-info">
          <div class="stat-val"><?= $availRooms ?></div>
          <div class="stat-lbl">Available Rooms</div>
        </div>
      </div>
      <div class="owner-stat-card">
        <div class="stat-icon" style="background:rgba(79,142,247,.15);color:var(--accent-blue)">
          <i class="bi bi-heart"></i>
        </div>
        <div class="stat-info">
          <div class="stat-val"><?= $totalInterests ?></div>
          <div class="stat-lbl">Student Interests</div>
        </div>
      </div>
      <div class="owner-stat-card">
        <div class="stat-icon" style="background:rgba(244,114,182,.15);color:var(--accent-pink)">
          <i class="bi bi-house-check"></i>
        </div>
        <div class="stat-info">
          <div class="stat-val"><?= $totalRooms - $availRooms ?></div>
          <div class="stat-lbl">Occupied Rooms</div>
        </div>
      </div>
    </div>

    <!-- Property Table -->
    <div class="owner-section">
      <h2 class="owner-section-title">My Properties</h2>

      <?php if (empty($properties)): ?>
      <div class="owner-empty">
        <div style="font-size:3rem;margin-bottom:1rem">🏢</div>
        <h5 style="color:var(--text-secondary)">No properties listed yet</h5>
        <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:1.2rem">Start by adding your first PG property</p>
        <a href="owner-add-property.php" class="btn-auth" style="display:inline-block;padding:.75rem 2rem;text-decoration:none;width:auto">
          <i class="bi bi-plus-circle"></i> Add First Property
        </a>
      </div>
      <?php else: ?>

      <!-- Toast placeholder -->
      <div id="owner-toast" style="display:none;position:fixed;bottom:2rem;right:2rem;z-index:9999"></div>

      <div class="table-wrap">
        <table class="owner-table">
          <thead>
            <tr>
              <th>Property</th>
              <th>City</th>
              <th>Price</th>
              <th>Availability</th>
              <th>Status</th>
              <th>Interests</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($properties as $p): ?>
          <tr id="prop-row-<?= $p['id'] ?>">
            <td>
              <div class="prop-name-cell">
                <img src="<?= e($p['image']) ?>" alt=""
                     onerror="this.src='https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=200'">
                <div>
                  <div style="font-weight:600;color:var(--text-primary)"><?= e($p['name']) ?></div>
                  <div style="font-size:.78rem;color:var(--text-muted)"><?= e(substr($p['address'],0,35)) ?>…</div>
                </div>
              </div>
            </td>
            <td><span style="color:var(--text-secondary)"><?= e($p['city']) ?></span></td>
            <td><span style="color:var(--gold-400);font-weight:700">₹<?= number_format($p['price'],0) ?></span>/mo</td>
            <td>
              <!-- Inline Availability Editor -->
              <div class="avail-editor" id="avail-<?= $p['id'] ?>">
                <div class="avail-display">
                  <span id="avail-val-<?= $p['id'] ?>" class="avail-num"><?= $p['available_rooms'] ?></span>
                  <span style="color:var(--text-muted);font-size:.8rem"> / <?= $p['total_rooms'] ?></span>
                  <button class="avail-edit-btn" onclick="openAvailEditor(<?= $p['id'] ?>, <?= $p['available_rooms'] ?>, <?= $p['total_rooms'] ?>)"
                          title="Edit availability">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                </div>
                <div class="avail-form" id="avail-form-<?= $p['id'] ?>" style="display:none">
                  <input type="number" id="avail-input-<?= $p['id'] ?>" min="0"
                         max="<?= $p['total_rooms'] ?>" value="<?= $p['available_rooms'] ?>"
                         class="avail-input" style="width:60px">
                  <span style="font-size:.8rem;color:var(--text-muted)">/ <?= $p['total_rooms'] ?></span>
                  <button class="avail-save-btn" onclick="saveAvail(<?= $p['id'] ?>, <?= $p['total_rooms'] ?>)">
                    <i class="bi bi-check-lg"></i>
                  </button>
                  <button class="avail-cancel-btn" onclick="closeAvailEditor(<?= $p['id'] ?>)">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>
              </div>
            </td>
            <td>
              <?php
              $statusMap = [
                'approved' => ['cls'=>'status-approved','label'=>'✅ Approved'],
                'pending'  => ['cls'=>'status-pending', 'label'=>'⏳ Pending'],
                'rejected' => ['cls'=>'status-rejected','label'=>'❌ Rejected'],
              ];
              $s = $statusMap[$p['status']] ?? $statusMap['pending'];
              ?>
              <span class="prop-status <?= $s['cls'] ?>"><?= $s['label'] ?></span>
            </td>
            <td>
              <span style="color:var(--accent-blue);font-weight:600">
                <i class="bi bi-heart-fill"></i> <?= $p['interest_count'] ?>
              </span>
            </td>
            <td>
              <div class="action-btns">
                <a href="property-detail.php?id=<?= $p['id'] ?>" class="action-btn view" title="View">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="owner-edit-property.php?id=<?= $p['id'] ?>" class="action-btn edit" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <button class="action-btn delete" title="Delete"
                        onclick="confirmDelete(<?= $p['id'] ?>, '<?= e(addslashes($p['name'])) ?>')">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- Footer -->
<footer><div class="container"><div class="footer-copy">© <?= date('Y') ?> PGFinder Owner Portal</div></div></footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
<script>
/* ── Availability Editor ─────────────────────────────────── */
function openAvailEditor(id, avail, total) {
  document.getElementById('avail-form-'+id).style.display='flex';
  document.querySelector('#avail-'+id+' .avail-display').style.display='none';
  document.getElementById('avail-input-'+id).focus();
}

function closeAvailEditor(id) {
  document.getElementById('avail-form-'+id).style.display='none';
  document.querySelector('#avail-'+id+' .avail-display').style.display='flex';
}

function saveAvail(id, total) {
  const val = parseInt(document.getElementById('avail-input-'+id).value);
  if (isNaN(val)||val<0||val>total) {
    showToast('Value must be between 0 and '+total, 'error'); return;
  }
  const fd = new FormData();
  fd.append('property_id', id);
  fd.append('available_rooms', val);

  fetch('api/owner_update_availability.php', {method:'POST', body:fd})
    .then(r=>r.json()).then(data => {
      if (data.success) {
        document.getElementById('avail-val-'+id).textContent = val;
        closeAvailEditor(id);
        showToast('Availability updated!', 'success');
      } else {
        showToast(data.message||'Failed to update.','error');
      }
    }).catch(()=>showToast('Network error','error'));
}

/* ── Delete Property ─────────────────────────────────────── */
function confirmDelete(id, name) {
  if (!confirm('Delete "'+name+'"? This cannot be undone.')) return;
  const fd = new FormData();
  fd.append('property_id', id);
  fetch('api/owner_delete_property.php', {method:'POST', body:fd})
    .then(r=>r.json()).then(data => {
      if (data.success) {
        document.getElementById('prop-row-'+id).remove();
        showToast('Property deleted.','info');
      } else {
        showToast(data.message||'Failed to delete.','error');
      }
    }).catch(()=>showToast('Network error','error'));
}
</script>
<?php include_once 'config/notification_modal.php'; ?>
</body></html>
