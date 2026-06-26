<?php
/**
 * admin/index.php — Admin Dashboard
 */
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$adminName = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$db        = getDB();

// ── Stats ────────────────────────────────────────────────
$totalProperties  = $db->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$pendingProperties= $db->query("SELECT COUNT(*) FROM properties WHERE status='pending'")->fetchColumn();
$approvedProperties=$db->query("SELECT COUNT(*) FROM properties WHERE status='approved'")->fetchColumn();
$totalUsers       = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$totalOwners      = $db->query("SELECT COUNT(*) FROM users WHERE role='owner'")->fetchColumn();
$totalInterests   = $db->query("SELECT COUNT(*) FROM interested_users")->fetchColumn();

// ── All Properties ───────────────────────────────────────
$properties = $db->query("
    SELECT p.*, u.name AS owner_name, u.email AS owner_email,
           COUNT(DISTINCT iu.id) AS interest_count
    FROM properties p
    LEFT JOIN users u ON u.id = p.owner_id
    LEFT JOIN interested_users iu ON iu.property_id = p.id
    GROUP BY p.id
    ORDER BY FIELD(p.status,'pending','approved','rejected'), p.created_at DESC
")->fetchAll();

// ── All Users ────────────────────────────────────────────
$users = $db->query("
    SELECT u.*, COUNT(DISTINCT iu.property_id) AS shortlisted
    FROM users u
    LEFT JOIN interested_users iu ON iu.user_id = u.id
    WHERE u.role IN ('student','owner')
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard — PGFinder</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/admin.css">
</head><body class="admin-body">

<!-- Admin Sidebar -->
<div class="admin-layout">

  <aside class="admin-sidebar">
    <div class="sidebar-brand">🛡 PGFinder Admin</div>
    <nav class="sidebar-nav">
      <a href="#dashboard" class="sidebar-link active" onclick="showTab('dashboard',this)">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
      <a href="#properties" class="sidebar-link" onclick="showTab('properties',this)">
        <i class="bi bi-building"></i> Properties
        <?php if($pendingProperties>0): ?>
        <span class="sidebar-badge"><?= $pendingProperties ?></span>
        <?php endif; ?>
      </a>
      <a href="#users" class="sidebar-link" onclick="showTab('users',this)">
        <i class="bi bi-people"></i> Users
      </a>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <i class="bi bi-person-circle"></i>
        <span><?= $adminName ?></span>
      </div>
      <a href="logout.php" class="sidebar-logout">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="admin-main">

    <!-- Top Bar -->
    <div class="admin-topbar">
      <h1 class="admin-page-title" id="page-title">Dashboard</h1>
      <div class="d-flex gap-2 align-items-center">
        <a href="../index.php" class="admin-topbar-link" target="_blank">
          <i class="bi bi-box-arrow-up-right"></i> View Site
        </a>
      </div>
    </div>

    <!-- ── TAB: Dashboard ─────────────────────────────── -->
    <div id="tab-dashboard" class="admin-tab active">

      <!-- Stat Cards -->
      <div class="admin-stats-grid">
        <div class="admin-stat-card">
          <div class="asc-icon" style="background:rgba(245,200,66,.15);color:var(--gold-400)">
            <i class="bi bi-building"></i>
          </div>
          <div class="asc-body">
            <div class="asc-val"><?= $totalProperties ?></div>
            <div class="asc-lbl">Total Properties</div>
          </div>
        </div>
        <div class="admin-stat-card">
          <div class="asc-icon" style="background:rgba(239,68,68,.15);color:#f87171">
            <i class="bi bi-hourglass-split"></i>
          </div>
          <div class="asc-body">
            <div class="asc-val"><?= $pendingProperties ?></div>
            <div class="asc-lbl">Pending Approval</div>
          </div>
        </div>
        <div class="admin-stat-card">
          <div class="asc-icon" style="background:rgba(34,197,94,.15);color:#4ade80">
            <i class="bi bi-check-circle"></i>
          </div>
          <div class="asc-body">
            <div class="asc-val"><?= $approvedProperties ?></div>
            <div class="asc-lbl">Approved Properties</div>
          </div>
        </div>
        <div class="admin-stat-card">
          <div class="asc-icon" style="background:rgba(79,142,247,.15);color:var(--accent-blue)">
            <i class="bi bi-people"></i>
          </div>
          <div class="asc-body">
            <div class="asc-val"><?= $totalUsers ?></div>
            <div class="asc-lbl">Students</div>
          </div>
        </div>
        <div class="admin-stat-card">
          <div class="asc-icon" style="background:rgba(45,212,191,.15);color:var(--accent-teal)">
            <i class="bi bi-person-badge"></i>
          </div>
          <div class="asc-body">
            <div class="asc-val"><?= $totalOwners ?></div>
            <div class="asc-lbl">PG Owners</div>
          </div>
        </div>
        <div class="admin-stat-card">
          <div class="asc-icon" style="background:rgba(244,114,182,.15);color:var(--accent-pink)">
            <i class="bi bi-heart"></i>
          </div>
          <div class="asc-body">
            <div class="asc-val"><?= $totalInterests ?></div>
            <div class="asc-lbl">Total Interests</div>
          </div>
        </div>
      </div>

      <!-- Pending Properties Quick View -->
      <?php
      $pendingList = array_filter($properties, fn($p) => $p['status']==='pending');
      if (!empty($pendingList)):
      ?>
      <div class="admin-section">
        <div class="admin-section-header">
          <h2 class="admin-section-title">
            <i class="bi bi-hourglass-split" style="color:#f87171"></i>
            Pending Approval (<?= count($pendingList) ?>)
          </h2>
          <button class="admin-tab-link" onclick="showTab('properties',null)">View All</button>
        </div>
        <?php foreach(array_slice($pendingList,0,3) as $p): ?>
        <div class="pending-item" id="pend-<?= $p['id'] ?>">
          <img src="<?= e($p['image']) ?>" alt=""
               onerror="this.src='https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=100'">
          <div class="pending-info">
            <div class="pending-name"><?= e($p['name']) ?></div>
            <div class="pending-meta"><?= e($p['city']) ?> · ₹<?= number_format($p['price'],0) ?>/mo · by <?= e($p['owner_name']??'Admin') ?></div>
          </div>
          <div class="pending-actions">
            <button class="admin-approve-btn" onclick="approveProperty(<?= $p['id'] ?>, 'approved')">
              <i class="bi bi-check-lg"></i> Approve
            </button>
            <button class="admin-reject-btn" onclick="approveProperty(<?= $p['id'] ?>, 'rejected')">
              <i class="bi bi-x-lg"></i> Reject
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div>

    <!-- ── TAB: Properties ────────────────────────────── -->
    <div id="tab-properties" class="admin-tab">
      <div class="admin-section">
        <div class="admin-section-header">
          <h2 class="admin-section-title"><i class="bi bi-building"></i> All Properties</h2>
          <input type="text" placeholder="🔍 Search…" class="admin-search" oninput="filterTable('prop-tbody',this.value)">
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr><th>Property</th><th>Owner</th><th>City</th><th>Price</th><th>Rooms</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody id="prop-tbody">
            <?php foreach($properties as $p): ?>
            <tr id="admin-prop-<?= $p['id'] ?>">
              <td>
                <div style="display:flex;align-items:center;gap:10px">
                  <img src="<?= e($p['image']) ?>" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:8px"
                       onerror="this.src='https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=100'">
                  <div>
                    <div style="font-weight:600;font-size:.9rem"><?= e($p['name']) ?></div>
                    <div style="font-size:.76rem;color:var(--text-muted)"><?= e(substr($p['address'],0,30)) ?>…</div>
                  </div>
                </div>
              </td>
              <td style="font-size:.85rem"><?= e($p['owner_name']??'Admin') ?></td>
              <td><?= e($p['city']) ?></td>
              <td style="color:var(--gold-400);font-weight:600">₹<?= number_format($p['price'],0) ?></td>
              <td>
                <span style="color:var(--accent-teal)"><?= $p['available_rooms'] ?></span>
                <span style="color:var(--text-muted);font-size:.8rem"> / <?= $p['total_rooms'] ?></span>
              </td>
              <td>
                <?php
                $sm = ['approved'=>['class'=>'badge-approved','lbl'=>'Approved'],
                       'pending' =>['class'=>'badge-pending', 'lbl'=>'Pending'],
                       'rejected'=>['class'=>'badge-rejected','lbl'=>'Rejected']];
                $st = $sm[$p['status']]??$sm['pending'];
                ?>
                <span class="admin-status-badge <?= $st['class'] ?>" id="status-badge-<?= $p['id'] ?>">
                  <?= $st['lbl'] ?>
                </span>
              </td>
              <td>
                <div style="display:flex;gap:6px">
                  <?php if($p['status']==='pending'): ?>
                  <button class="admin-approve-btn" onclick="approveProperty(<?= $p['id'] ?>,'approved')">✅ Approve</button>
                  <button class="admin-reject-btn"  onclick="approveProperty(<?= $p['id'] ?>,'rejected')">❌ Reject</button>
                  <?php elseif($p['status']==='approved'): ?>
                  <button class="admin-reject-btn"  onclick="approveProperty(<?= $p['id'] ?>,'rejected')">❌ Reject</button>
                  <?php else: ?>
                  <button class="admin-approve-btn" onclick="approveProperty(<?= $p['id'] ?>,'approved')">✅ Approve</button>
                  <?php endif; ?>
                  <a href="../property-detail.php?id=<?= $p['id'] ?>" class="action-btn view" target="_blank" title="View">
                    <i class="bi bi-eye"></i>
                  </a>
                  <button class="action-btn delete" onclick="adminDeleteProp(<?= $p['id'] ?>,'<?= e(addslashes($p['name'])) ?>')" title="Delete">
                    <i class="bi bi-trash3"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── TAB: Users ─────────────────────────────────── -->
    <div id="tab-users" class="admin-tab">
      <div class="admin-section">
        <div class="admin-section-header">
          <h2 class="admin-section-title"><i class="bi bi-people"></i> All Users</h2>
          <input type="text" placeholder="🔍 Search…" class="admin-search" oninput="filterTable('users-tbody',this.value)">
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Shortlisted</th><th>Joined</th><th>Action</th></tr>
            </thead>
            <tbody id="users-tbody">
            <?php foreach($users as $u): ?>
            <tr id="admin-user-<?= $u['id'] ?>">
              <td style="font-weight:600"><?= e($u['name']) ?></td>
              <td style="font-size:.85rem;color:var(--text-secondary)"><?= e($u['email']) ?></td>
              <td style="font-size:.85rem"><?= e($u['phone']??'—') ?></td>
              <td>
                <span class="admin-status-badge <?= $u['role']==='owner'?'badge-owner':'badge-student' ?>">
                  <?= ucfirst($u['role']) ?>
                </span>
              </td>
              <td style="text-align:center"><?= $u['shortlisted'] ?></td>
              <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
              <td>
                <button class="action-btn delete" title="Delete user"
                        onclick="adminDeleteUser(<?= $u['id'] ?>,'<?= e(addslashes($u['name'])) ?>')">
                  <i class="bi bi-trash3"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/main.js"></script>
<script>
/* ── Tab switching ───────────────────────────────────── */
function showTab(name, el) {
  document.querySelectorAll('.admin-tab').forEach(t=>t.classList.remove('active'));
  document.querySelectorAll('.sidebar-link').forEach(l=>l.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  document.getElementById('page-title').textContent =
    name==='dashboard'?'Dashboard':name==='properties'?'Properties':'Users';
  if(el) el.classList.add('active');
  else {
    document.querySelectorAll('.sidebar-link').forEach(l=>{
      if(l.getAttribute('href')==='#'+name) l.classList.add('active');
    });
  }
  return false;
}

/* ── Approve / Reject Property ───────────────────────── */
function approveProperty(id, action) {
  const fd = new FormData();
  fd.append('property_id', id); fd.append('action', action);
  fetch('../api/admin_approve_property.php',{method:'POST',body:fd})
    .then(r=>r.json()).then(data=>{
      if(data.success){
        const badge = document.getElementById('status-badge-'+id);
        if(badge){
          badge.className='admin-status-badge '+(action==='approved'?'badge-approved':'badge-rejected');
          badge.textContent=action==='approved'?'Approved':'Rejected';
        }
        const pend = document.getElementById('pend-'+id);
        if(pend) pend.remove();
        showToast(data.message, 'success');
      } else showToast(data.message,'error');
    }).catch(()=>showToast('Network error','error'));
}

/* ── Admin Delete Property ───────────────────────────── */
function adminDeleteProp(id, name) {
  if(!confirm('Delete property "'+name+'"? This is permanent.')) return;
  const fd=new FormData(); fd.append('property_id',id);
  fetch('../api/owner_delete_property.php',{method:'POST',body:fd})
    .then(r=>r.json()).then(data=>{
      if(data.success){document.getElementById('admin-prop-'+id)?.remove();showToast('Deleted','info');}
      else showToast(data.message,'error');
    }).catch(()=>showToast('Network error','error'));
}

/* ── Admin Delete User ───────────────────────────────── */
function adminDeleteUser(id, name) {
  if(!confirm('Delete user "'+name+'"? All their data will be removed.')) return;
  const fd=new FormData(); fd.append('user_id',id);
  fetch('../api/admin_delete_user.php',{method:'POST',body:fd})
    .then(r=>r.json()).then(data=>{
      if(data.success){document.getElementById('admin-user-'+id)?.remove();showToast('User removed','info');}
      else showToast(data.message,'error');
    }).catch(()=>showToast('Network error','error'));
}

/* ── Table Search Filter ─────────────────────────────── */
function filterTable(tbodyId, query) {
  const q = query.toLowerCase();
  document.querySelectorAll('#'+tbodyId+' tr').forEach(row=>{
    row.style.display = row.textContent.toLowerCase().includes(q)?'':'none';
  });
}
</script>
</body></html>
