/**
 * main.js — Global utilities, toast notifications, loading helpers
 * Student Accommodation Platform
 */

/* ── Toast System ─────────────────────────────────────────── */
(function () {
  let container = null;

  function getContainer() {
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container-custom';
      document.body.appendChild(container);
    }
    return container;
  }

  /**
   * Show a toast notification.
   * @param {string} message
   * @param {'success'|'error'|'info'} type
   * @param {number} duration ms
   */
  window.showToast = function (message, type = 'info', duration = 3000) {
    const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', info: 'bi-info-circle-fill' };
    const toast = document.createElement('div');
    toast.className = `toast-custom toast-${type}`;
    toast.innerHTML = `
      <i class="bi ${icons[type]} toast-icon"></i>
      <span class="toast-text">${message}</span>
    `;
    getContainer().appendChild(toast);
    setTimeout(() => toast.remove(), duration + 400);
  };
})();

/* ── Loading Spinner ──────────────────────────────────────── */
/**
 * Show a loading overlay inside a container element.
 * @param {HTMLElement} el — container element
 * @returns {HTMLElement} overlay (call overlay.remove() to hide)
 */
window.showLoading = function (el) {
  el.style.position = 'relative';
  const overlay = document.createElement('div');
  overlay.className = 'loading-overlay';
  overlay.innerHTML = '<div class="spinner-custom"></div>';
  el.appendChild(overlay);
  return overlay;
};

/* ── Format Currency ──────────────────────────────────────── */
window.formatCurrency = function (amount) {
  return '₹' + Number(amount).toLocaleString('en-IN');
};

/* ── Star Rating HTML ─────────────────────────────────────── */
window.renderStars = function (rating) {
  const full  = Math.floor(rating);
  const half  = rating % 1 >= 0.5 ? 1 : 0;
  const empty = 5 - full - half;
  return (
    '<i class="bi bi-star-fill"></i>'.repeat(full) +
    (half ? '<i class="bi bi-star-half"></i>' : '') +
    '<i class="bi bi-star"></i>'.repeat(empty)
  );
};

/* ── Gender Badge ─────────────────────────────────────────── */
window.genderBadge = function (gender) {
  const map = {
    male:   { cls: 'badge-male',   label: '♂ Boys' },
    female: { cls: 'badge-female', label: '♀ Girls' },
    any:    { cls: 'badge-any',    label: '⚥ Any' },
  };
  const { cls, label } = map[gender] || map['any'];
  return `<span class="card-gender-badge ${cls}">${label}</span>`;
};

/* ── Toggle Interest Button ───────────────────────────────── */
window.toggleInterest = function (propertyId, btn) {
  if (!window._isLoggedIn) {
    showToast('Please login to save properties!', 'info');
    setTimeout(() => { window.location.href = 'login.php'; }, 1200);
    return;
  }

  btn.disabled = true;
  const formData = new FormData();
  formData.append('property_id', propertyId);

  fetch('api/toggle_interest.php', {
    method: 'POST',
    body: formData,
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const isInt = data.is_interested;
        // Update all buttons for this property on the page
        document.querySelectorAll(`[data-property-id="${propertyId}"]`).forEach(el => {
          el.classList.toggle('active', isInt);
          if (el.tagName === 'BUTTON' && el.classList.contains('btn-interest')) {
            el.innerHTML = isInt
              ? '<i class="bi bi-heart-fill"></i> Shortlisted!'
              : '<i class="bi bi-heart"></i> Mark as Interested';
            el.classList.toggle('interested', isInt);
          }
        });
        showToast(data.message, isInt ? 'success' : 'info');
      } else {
        showToast(data.message || 'Something went wrong.', 'error');
      }
    })
    .catch(() => showToast('Network error. Please try again.', 'error'))
    .finally(() => { btn.disabled = false; });
};

/* ── Navbar scroll effect ─────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  const navbar = document.querySelector('.navbar-custom');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.style.boxShadow = window.scrollY > 20
        ? '0 4px 30px rgba(0,0,0,0.5)'
        : '';
    });
  }
});
