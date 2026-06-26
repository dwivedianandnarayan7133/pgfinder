/**
 * filters.js — AJAX-based property filter & search
 * Student Accommodation Platform
 */

document.addEventListener('DOMContentLoaded', function () {
  /* ── State ─────────────────────────────────────────────── */
  const state = {
    city:      'all',
    gender:    '',
    maxPrice:  20000,
    search:    '',
    debounceTimer: null,
  };

  /* ── DOM Refs ───────────────────────────────────────────── */
  const grid         = document.getElementById('property-grid');
  const resultLabel  = document.getElementById('result-count');
  const priceSlider  = document.getElementById('price-slider');
  const priceDisplay = document.getElementById('price-display');
  const searchInput  = document.getElementById('search-input');
  const searchBtn    = document.getElementById('search-btn');

  if (!grid) return; // not on listing page

  /* ── Fetch & Render ─────────────────────────────────────── */
  function fetchProperties() {
    const overlay = showLoading(grid);

    const params = new URLSearchParams({
      city:      state.city,
      gender:    state.gender,
      max_price: state.maxPrice,
      min_price: 0,
      search:    state.search,
    });

    fetch('api/get_properties.php?' + params.toString())
      .then(r => r.json())
      .then(data => {
        overlay.remove();
        if (!data.success) {
          grid.innerHTML = '<p class="text-center text-muted-custom py-5">Failed to load properties.</p>';
          return;
        }
        renderCards(data.data);
        if (resultLabel) {
          resultLabel.innerHTML = `Showing <strong>${data.data.length}</strong> ${data.data.length === 1 ? 'property' : 'properties'}`;
        }
      })
      .catch(() => {
        overlay.remove();
        grid.innerHTML = '<p class="text-center text-muted-custom py-5">Network error. Please refresh.</p>';
      });
  }

  /* ── Card HTML ──────────────────────────────────────────── */
  function renderCards(properties) {
    if (properties.length === 0) {
      grid.innerHTML = `
        <div class="col-12 text-center py-5">
          <div style="font-size:3.5rem;margin-bottom:1rem;">🏠</div>
          <h5 style="color:var(--text-secondary)">No properties found</h5>
          <p style="color:var(--text-muted);font-size:.9rem">Try adjusting your filters</p>
        </div>`;
      return;
    }

    grid.innerHTML = properties.map(p => {
      const amenityTags = p.amenities_preview
        ? p.amenities_preview.split(', ').slice(0, 3)
            .map(a => `<span class="amenity-tag">${a}</span>`).join('')
        : '';

      const wishlistActive = p.is_interested ? 'active' : '';

      return `
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="property-card" onclick="window.location.href='property-detail.php?id=${p.id}'">
            <div class="card-img-wrap">
              <img src="${p.image}" alt="${p.name}" loading="lazy"
                   onerror="this.src='https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800'">
              ${genderBadge(p.gender)}
              <button class="card-wishlist-btn ${wishlistActive}"
                      data-property-id="${p.id}"
                      onclick="event.stopPropagation(); toggleInterest(${p.id}, this)"
                      title="Save to Shortlist"
                      id="wish-btn-${p.id}">
                <i class="bi bi-heart${p.is_interested ? '-fill' : ''}"></i>
              </button>
            </div>
            <div class="card-body-custom">
              <div class="card-property-name">${p.name}</div>
              <div class="card-location">
                <i class="bi bi-geo-alt-fill" style="color:var(--gold-400)"></i>
                ${p.city} &bull; ${p.address.substring(0, 40)}…
              </div>
              <div class="card-amenities-preview">${amenityTags}</div>
              <div class="card-footer-custom">
                <div class="card-price">${formatCurrency(p.price)}<span>/mo</span></div>
                <div class="card-rating">
                  <i class="bi bi-star-fill"></i>
                  ${parseFloat(p.rating).toFixed(1)}
                </div>
              </div>
              <a href="property-detail.php?id=${p.id}" class="btn-view-details" onclick="event.stopPropagation()">
                View Details <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>`;
    }).join('');
  }

  /* ── City Filter ────────────────────────────────────────── */
  document.querySelectorAll('.city-chip').forEach(chip => {
    chip.addEventListener('click', function () {
      document.querySelectorAll('.city-chip').forEach(c => c.classList.remove('active'));
      this.classList.add('active');
      state.city = this.dataset.city;
      fetchProperties();
    });
  });

  /* ── Gender Filter ──────────────────────────────────────── */
  document.querySelectorAll('.gender-chip').forEach(chip => {
    chip.addEventListener('click', function () {
      if (this.classList.contains('active')) {
        this.classList.remove('active');
        state.gender = '';
      } else {
        document.querySelectorAll('.gender-chip').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        state.gender = this.dataset.gender;
      }
      fetchProperties();
    });
  });

  /* ── Price Slider ───────────────────────────────────────── */
  if (priceSlider) {
    priceSlider.addEventListener('input', function () {
      state.maxPrice = this.value;
      if (priceDisplay) priceDisplay.textContent = formatCurrency(this.value);
    });

    priceSlider.addEventListener('change', fetchProperties);
  }

  /* ── Search ─────────────────────────────────────────────── */
  function doSearch() {
    state.search = searchInput ? searchInput.value.trim() : '';
    fetchProperties();
  }

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      clearTimeout(state.debounceTimer);
      state.debounceTimer = setTimeout(doSearch, 450);
    });

    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { clearTimeout(state.debounceTimer); doSearch(); }
    });
  }

  if (searchBtn) {
    searchBtn.addEventListener('click', function () {
      clearTimeout(state.debounceTimer); doSearch();
    });
  }

  /* ── Apply / Reset Buttons ──────────────────────────────── */
  const applyBtn = document.getElementById('apply-filters');
  const resetBtn = document.getElementById('reset-filters');

  if (applyBtn) applyBtn.addEventListener('click', fetchProperties);

  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      state.city = 'all'; state.gender = ''; state.maxPrice = 20000; state.search = '';
      document.querySelectorAll('.city-chip, .gender-chip').forEach(c => c.classList.remove('active'));
      document.querySelector('.city-chip[data-city="all"]')?.classList.add('active');
      if (priceSlider)  priceSlider.value = 20000;
      if (priceDisplay) priceDisplay.textContent = formatCurrency(20000);
      if (searchInput)  searchInput.value = '';
      fetchProperties();
    });
  }

  /* ── Initial Load ───────────────────────────────────────── */
  fetchProperties();
});
