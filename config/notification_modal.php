<?php
/**
 * config/notification_modal.php — Registration Notification Modal
 * Included in index.php and owner-dashboard.php to show credentials and send via WhatsApp.
 */
startSession();
if (isset($_SESSION['just_registered'])):
    $jr = $_SESSION['just_registered'];
    unset($_SESSION['just_registered']);

    // Prepare WhatsApp URL for completely free sending
    $waUrl = '';
    if (!empty($jr['phone'])) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $jr['phone']);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone; // Prepend India country code by default for 10-digit numbers
        }
        
        $msgText = "Hello " . $jr['name'] . "! Your PGFinder account is created. Your login details:\n"
                 . "- Username/Email: " . $jr['email'] . "\n"
                 . "- Password: " . $jr['password'] . "\n"
                 . "Log in at: http://localhost/student-accommodation/login.php";
                 
        $waUrl = "https://api.whatsapp.com/send?phone=" . $cleanPhone . "&text=" . rawurlencode($msgText);
    }
?>
<div class="modal fade" id="regSuccessModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(8px);">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background: #131c30; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.6); color: #f1f5f9; overflow: hidden;">
      
      <!-- Header -->
      <div class="modal-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding: 1.25rem 1.5rem; background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0));">
        <h5 class="modal-title" style="color: #f5c842; font-weight: 800; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
          <span style="font-size: 1.3rem;">🎉</span> Account Registered!
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem; opacity: 0.7;"></button>
      </div>

      <div class="modal-body" style="padding: 1.8rem 1.5rem;">
        <p style="font-size: 0.95rem; line-height: 1.6; color: #cbd5e1; margin-bottom: 1.2rem;">
          Welcome to PGFinder, <strong><?= htmlspecialchars($jr['name']) ?></strong>! Your account has been created successfully.
        </p>

        <!-- Credentials Info Box -->
        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 12px; padding: 1.1rem; margin-bottom: 1.2rem; box-shadow: inset 0 2px 8px rgba(0,0,0,0.2);">
          <div style="font-weight: 700; color: #f5c842; font-size: 0.82rem; margin-bottom: 0.6rem; display: flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px;">
            <i class="bi bi-shield-lock-fill" style="font-size: 0.95rem;"></i> Your Credentials
          </div>
          <div style="background: rgba(0,0,0,0.2); border-radius: 8px; padding: 0.8rem 1rem; font-family: monospace; font-size: 0.78rem; color: #cbd5e1; line-height: 1.6;">
            <span style="color: #38bdf8;">Username/Email:</span> <?= htmlspecialchars($jr['email']) ?><br>
            <span style="color: #38bdf8;">Password:</span> <?= htmlspecialchars($jr['password']) ?>
          </div>
        </div>

        <!-- Free Browser-based WhatsApp Button -->
        <?php if (!empty($waUrl)): ?>
        <div style="margin-top: 1.5rem; text-align: center;">
          <a href="<?= $waUrl ?>" target="_blank" class="btn btn-success" 
             style="background: #25d366; border: none; color: #fff; font-weight: 700; border-radius: 12px; padding: 0.75rem 1.5rem; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; text-decoration: none; width: 100%; justify-content: center; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3); transition: transform 0.2s;"
             onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
            <i class="bi bi-whatsapp" style="font-size: 1.1rem;"></i>
            Send Credentials via WhatsApp
          </a>
          <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 6px; margin-bottom: 0;">
            * Instantly launch WhatsApp Web/App to send pre-filled login details for free!
          </p>
        </div>
        <?php else: ?>
        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 8px; padding: 0.8rem; font-size: 0.78rem; color: #94a3b8; text-align: center;">
          <i class="bi bi-telephone-x"></i> No contact phone number was provided to enable WhatsApp forwarding.
        </div>
        <?php endif; ?>

        <!-- Backup local log info -->
        <p style="font-size: 0.72rem; color: #64748b; text-align: center; margin-top: 1.5rem; margin-bottom: 0;">
          A copy of these credentials has also been written to <code>notifications_log.txt</code> in the project folder.
        </p>

      </div>

      <div class="modal-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.08); padding: 1rem 1.5rem; background: rgba(0,0,0,0.15); display: flex; justify-content: flex-end;">
        <button type="button" class="btn-auth" data-bs-dismiss="modal" style="width: auto; padding: 0.6rem 1.8rem; background: linear-gradient(135deg, #e8b820, #f5c842); color: #0a0f1e; border: none; font-weight: 700; border-radius: 10px; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 12px rgba(245, 200, 66, 0.2);" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
          Continue to Dashboard
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const modalEl = document.getElementById('regSuccessModal');
  if (modalEl) {
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  }
});
</script>
<?php endif; ?>
