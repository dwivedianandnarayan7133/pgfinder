<?php
/**
 * owner-add-property.php — Add New Property
 */
require_once 'config/db.php';
requireOwner();

$userId   = currentUserId();
$db       = getDB();
$error    = '';
$success  = '';

// Fetch all amenities for checkboxes
$amenities = $db->query("SELECT * FROM amenities ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']         ?? '');
    $city        = trim($_POST['city']         ?? '');
    $address     = trim($_POST['address']      ?? '');
    $price       = (float)($_POST['price']     ?? 0);
    $gender      = $_POST['gender']            ?? 'any';
    $description = trim($_POST['description']  ?? '');
    $image       = trim($_POST['image']        ?? '');
    $totalRooms  = (int)($_POST['total_rooms'] ?? 1);
    $availRooms  = (int)($_POST['avail_rooms'] ?? 0);
    $contactPhone= trim($_POST['contact_phone'] ?? '');
    $selectedAm  = $_POST['amenities'] ?? [];

    if (empty($name)||empty($city)||empty($address)||$price<=0)
        $error = 'Name, city, address, and price are required.';
    elseif (!in_array($gender,['male','female','any']))
        $error = 'Invalid gender type.';
    elseif ($availRooms > $totalRooms)
        $error = 'Available rooms cannot exceed total rooms.';
    else {
        if (empty($image)) $image = 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800';

        $ins = $db->prepare("
            INSERT INTO properties
              (owner_id,name,city,address,price,gender,description,image,total_rooms,available_rooms,status,contact_phone)
            VALUES (:oid,:name,:city,:addr,:price,:gender,:desc,:img,:tr,:ar,'pending',:cp)
        ");
        $ins->execute([
            ':oid'=>$userId,':name'=>$name,':city'=>$city,':addr'=>$address,
            ':price'=>$price,':gender'=>$gender,':desc'=>$description,
            ':img'=>$image,':tr'=>$totalRooms,':ar'=>$availRooms,':cp'=>$contactPhone
        ]);
        $propId = $db->lastInsertId();

        // Link amenities
        if (!empty($selectedAm)) {
            $amStmt = $db->prepare("INSERT IGNORE INTO property_amenities (property_id,amenity_id) VALUES (?,?)");
            foreach ($selectedAm as $amId) {
                $amStmt->execute([$propId, (int)$amId]);
            }
        }
        redirect('owner-dashboard.php');
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Property — PGFinder Owner</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/owner.css">
</head><body>

<nav class="navbar-custom">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="navbar-brand-custom"><span class="brand-icon">🏠</span> PGFinder</a>
    <div class="d-flex gap-2 align-items-center">
      <span class="owner-badge"><i class="bi bi-building"></i> Owner Portal</span>
      <a href="owner-dashboard.php" class="btn-nav-login">
        <i class="bi bi-arrow-left"></i> Dashboard
      </a>
    </div>
  </div>
</nav>

<div class="owner-page">
  <div class="container" style="max-width:800px">
    <div class="owner-header">
      <div>
        <p class="section-label">🏢 Owner Portal</p>
        <h1 class="section-title">Add New Property</h1>
        <div class="divider-gold"></div>
      </div>
    </div>

    <?php if($error): ?>
    <div class="alert-custom alert-error" style="margin-bottom:1.5rem">
      <i class="bi bi-exclamation-circle"></i> <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="owner-form">

      <!-- Basic Info -->
      <div class="owner-form-section">
        <h3 class="owner-form-title"><i class="bi bi-info-circle" style="color:var(--gold-400)"></i> Basic Information</h3>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label-custom">Property Name *</label>
            <input type="text" class="form-input-custom" name="name"
                   placeholder="e.g. Sunrise Boys PG" required value="<?= e($_POST['name']??'') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label-custom">City *</label>
            <select class="form-input-custom" name="city" required>
              <option value="">Select City</option>
              <?php foreach(['Bangalore','Pune','Hyderabad','Chennai','Delhi','Mumbai','Kolkata','Ahmedabad'] as $c): ?>
              <option value="<?= $c ?>" <?= (($_POST['city']??'')===$c)?'selected':'' ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label-custom">Gender Type *</label>
            <select class="form-input-custom" name="gender">
              <option value="male"   <?= (($_POST['gender']??'')==='male'  )?'selected':''?>>Boys Only</option>
              <option value="female" <?= (($_POST['gender']??'')==='female')?'selected':''?>>Girls Only</option>
              <option value="any"    <?= (($_POST['gender']??'any')==='any')?'selected':''?>>Co-ed / Any</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label-custom">Full Address *</label>
            <input type="text" class="form-input-custom" name="address"
                   placeholder="Street, Area, City - Pincode" required value="<?= e($_POST['address']??'') ?>">
          </div>
        </div>
      </div>

      <!-- Pricing & Rooms -->
      <div class="owner-form-section">
        <h3 class="owner-form-title"><i class="bi bi-currency-rupee" style="color:var(--gold-400)"></i> Pricing & Rooms</h3>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label-custom">Monthly Rent (₹) *</label>
            <input type="number" class="form-input-custom" name="price"
                   placeholder="e.g. 8500" min="1000" required value="<?= e($_POST['price']??'') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label-custom">Total Rooms</label>
            <input type="number" class="form-input-custom" name="total_rooms"
                   placeholder="e.g. 20" min="1" value="<?= e($_POST['total_rooms']??'10') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label-custom">Available Rooms</label>
            <input type="number" class="form-input-custom" name="avail_rooms"
                   placeholder="e.g. 5" min="0" value="<?= e($_POST['avail_rooms']??'5') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label-custom">Contact Phone</label>
            <input type="tel" class="form-input-custom" name="contact_phone"
                   placeholder="Owner contact number" value="<?= e($_POST['contact_phone']??'') ?>">
          </div>
        </div>
      </div>

      <!-- Description & Image -->
      <div class="owner-form-section">
        <h3 class="owner-form-title"><i class="bi bi-card-text" style="color:var(--gold-400)"></i> Description & Image</h3>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label-custom">Description</label>
            <textarea class="form-input-custom" name="description" rows="4"
                      placeholder="Describe your PG — facilities, location advantages, house rules…"><?= e($_POST['description']??'') ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label-custom">Image URL</label>
            <input type="url" class="form-input-custom" name="image"
                   id="image-url"
                   placeholder="https://… (leave blank for default)"
                   value="<?= e($_POST['image']??'') ?>"
                   oninput="previewImage(this.value)">
            <div id="img-preview" style="margin-top:10px;display:none">
              <img id="preview-img" src="" alt="Preview"
                   style="width:100%;max-height:200px;object-fit:cover;border-radius:var(--radius-md);border:1px solid var(--glass-border)">
            </div>
          </div>
        </div>
      </div>

      <!-- Amenities -->
      <div class="owner-form-section">
        <h3 class="owner-form-title"><i class="bi bi-grid-3x3-gap" style="color:var(--gold-400)"></i> Amenities</h3>
        <div class="amenity-checkbox-grid">
          <?php foreach($amenities as $am): ?>
          <label class="amenity-checkbox-item">
            <input type="checkbox" name="amenities[]" value="<?= $am['id'] ?>"
                   <?= in_array($am['id'], $_POST['amenities']??[])?'checked':'' ?>>
            <span class="amenity-check-content">
              <i class="bi <?= e($am['icon']) ?>"></i>
              <?= e($am['name']) ?>
            </span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="background:rgba(245,200,66,.08);border:1px solid rgba(245,200,66,.2);border-radius:10px;padding:1rem;font-size:.85rem;color:var(--text-secondary);margin-bottom:1.5rem">
        <i class="bi bi-info-circle" style="color:var(--gold-400)"></i>
        Your property will be reviewed by our admin team before going live. Approval usually takes 24 hours.
      </div>

      <div class="d-flex gap-3">
        <button type="submit" class="btn-auth" style="flex:1">
          <i class="bi bi-cloud-upload"></i> Submit Property
        </button>
        <a href="owner-dashboard.php" class="btn-filter-reset" style="flex:0.3;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center">
          Cancel
        </a>
      </div>

    </form>
  </div>
</div>

<footer><div class="container"><div class="footer-copy">© <?= date('Y') ?> PGFinder Owner Portal</div></div></footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewImage(url) {
  const wrap = document.getElementById('img-preview');
  const img  = document.getElementById('preview-img');
  if (url.trim()) {
    img.src = url; wrap.style.display='block';
    img.onerror = function(){ wrap.style.display='none'; };
  } else {
    wrap.style.display='none';
  }
}
</script>
</body></html>
