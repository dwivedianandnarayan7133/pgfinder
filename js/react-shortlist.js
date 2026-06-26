/**
 * react-shortlist.js — React 18 Shortlist Component
 * Student Accommodation Platform
 * Loaded via CDN in shortlist.php
 */

const { useState, useEffect, useCallback } = React;

/* ── Sub-components ─────────────────────────────────────── */

function StarRating({ rating }) {
  const full  = Math.floor(rating);
  const half  = rating % 1 >= 0.5;
  const empty = 5 - full - (half ? 1 : 0);
  return React.createElement('span', { className: 'rating-stars', style: { fontSize: '.85rem' } },
    ...Array(full).fill(null).map((_, i) =>
      React.createElement('i', { key: `f${i}`, className: 'bi bi-star-fill' })),
    half ? React.createElement('i', { key: 'h', className: 'bi bi-star-half' }) : null,
    ...Array(empty).fill(null).map((_, i) =>
      React.createElement('i', { key: `e${i}`, className: 'bi bi-star' }))
  );
}

function GenderBadge({ gender }) {
  const map = {
    male:   { cls: 'badge-male',   label: '♂ Boys' },
    female: { cls: 'badge-female', label: '♀ Girls' },
    any:    { cls: 'badge-any',    label: '⚥ Any' },
  };
  const { cls, label } = map[gender] || map.any;
  return React.createElement('span', { className: `card-gender-badge ${cls}`, style: { position: 'static', display:'inline-block', marginLeft:'8px' } }, label);
}

function PropertyCard({ property, onRemove }) {
  const [removing, setRemoving] = useState(false);

  function handleRemove() {
    if (removing) return;
    setRemoving(true);
    const fd = new FormData();
    fd.append('property_id', property.id);
    fetch('api/toggle_interest.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          onRemove(property.id);
        } else {
          setRemoving(false);
          alert(data.message || 'Failed to remove.');
        }
      })
      .catch(() => { setRemoving(false); alert('Network error.'); });
  }

  const price = '₹' + Number(property.price).toLocaleString('en-IN');

  return React.createElement('div', {
    className: 'col-lg-4 col-md-6 mb-4',
    style: { animation: 'fadeInUp 0.4s ease' }
  },
    React.createElement('div', { className: 'property-card', style: { height: '100%' } },
      React.createElement('div', { className: 'card-img-wrap' },
        React.createElement('img', {
          src: property.image,
          alt: property.name,
          loading: 'lazy',
          onError: e => { e.target.src = 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800'; }
        }),
        React.createElement('span', {
          className: 'card-gender-badge ' + (property.gender === 'male' ? 'badge-male' : property.gender === 'female' ? 'badge-female' : 'badge-any')
        }, property.gender === 'male' ? '♂ Boys' : property.gender === 'female' ? '♀ Girls' : '⚥ Any')
      ),
      React.createElement('div', { className: 'card-body-custom' },
        React.createElement('div', { className: 'card-property-name' }, property.name),
        React.createElement('div', { className: 'card-location' },
          React.createElement('i', { className: 'bi bi-geo-alt-fill', style: { color: 'var(--gold-400)' } }),
          ` ${property.city}`
        ),
        React.createElement('div', { className: 'card-footer-custom', style: { marginTop: 'auto' } },
          React.createElement('div', { className: 'card-price' },
            price,
            React.createElement('span', null, '/mo')
          ),
          React.createElement('div', { className: 'card-rating' },
            React.createElement('i', { className: 'bi bi-star-fill' }),
            ` ${parseFloat(property.rating).toFixed(1)}`
          )
        ),
        React.createElement('div', { style: { display: 'flex', gap: '8px', marginTop: '0.8rem' } },
          React.createElement('a', {
            href: `property-detail.php?id=${property.id}`,
            className: 'btn-view-details',
            style: { flex: 1 }
          }, 'View Details ', React.createElement('i', { className: 'bi bi-arrow-right' })),
          React.createElement('button', {
            onClick: handleRemove,
            disabled: removing,
            style: {
              background: 'rgba(239,68,68,0.1)',
              border: '1px solid rgba(239,68,68,0.3)',
              color: removing ? 'var(--text-muted)' : '#fca5a5',
              borderRadius: 'var(--radius-sm)',
              padding: '0 14px',
              cursor: removing ? 'not-allowed' : 'pointer',
              transition: 'all .3s',
              fontSize: '.85rem',
              fontFamily: 'Inter, sans-serif',
            },
            title: 'Remove from shortlist'
          }, removing
            ? React.createElement('i', { className: 'bi bi-hourglass-split' })
            : React.createElement('i', { className: 'bi bi-trash3' })
          )
        )
      )
    )
  );
}

/* ── Main Shortlist App ─────────────────────────────────── */

function ShortlistApp() {
  const [properties, setProperties] = useState([]);
  const [loading, setLoading]       = useState(true);
  const [error, setError]           = useState(null);

  const load = useCallback(() => {
    setLoading(true);
    fetch('api/get_shortlist.php')
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          setProperties(data.data);
        } else {
          setError(data.message || 'Failed to load shortlist.');
        }
      })
      .catch(() => setError('Network error. Please refresh.'))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => { load(); }, [load]);

  function handleRemove(id) {
    setProperties(prev => prev.filter(p => p.id !== id));
  }

  /* Loading state */
  if (loading) {
    return React.createElement('div', { style: { textAlign: 'center', padding: '5rem 0' } },
      React.createElement('div', { className: 'spinner-custom', style: { margin: '0 auto' } }),
      React.createElement('p', { style: { color: 'var(--text-secondary)', marginTop: '1rem', fontSize: '.9rem' } }, 'Loading your shortlist…')
    );
  }

  /* Error state */
  if (error) {
    return React.createElement('div', { className: 'text-center py-5' },
      React.createElement('i', { className: 'bi bi-exclamation-triangle', style: { fontSize: '2.5rem', color: '#fca5a5' } }),
      React.createElement('p', { style: { color: '#fca5a5', marginTop: '1rem' } }, error)
    );
  }

  /* Empty state */
  if (properties.length === 0) {
    return React.createElement('div', { className: 'shortlist-empty' },
      React.createElement('div', { className: 'empty-icon' }, '💔'),
      React.createElement('h4', { style: { color: 'var(--text-secondary)', marginBottom: '.5rem' } }, 'No shortlisted properties yet'),
      React.createElement('p', { style: { color: 'var(--text-muted)', fontSize: '.9rem', marginBottom: '1.5rem' } },
        'Browse properties and click the ❤ icon to save your favourites here.'
      ),
      React.createElement('a', { href: 'index.php', className: 'btn-auth', style: { display:'inline-block', padding:'0.75rem 2rem', textDecoration:'none', width:'auto' } },
        '🏠 Browse Properties'
      )
    );
  }

  /* Property grid */
  return React.createElement('div', null,
    React.createElement('div', {
      style: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: '1.5rem'
      }
    },
      React.createElement('p', { className: 'result-count' },
        'You have shortlisted ',
        React.createElement('strong', null, properties.length),
        ` ${properties.length === 1 ? 'property' : 'properties'}`
      )
    ),
    React.createElement('div', { className: 'row' },
      ...properties.map(p =>
        React.createElement(PropertyCard, { key: p.id, property: p, onRemove: handleRemove })
      )
    )
  );
}

/* ── Mount ──────────────────────────────────────────────── */
const rootEl = document.getElementById('react-shortlist-root');
if (rootEl) {
  ReactDOM.createRoot(rootEl).render(React.createElement(ShortlistApp));
}
