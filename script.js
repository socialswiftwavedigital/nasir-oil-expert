/* ===== PRELOADER ===== */
window.addEventListener('load', () => {
  setTimeout(() => {
    const pre = document.getElementById('preloader');
    if (pre) pre.classList.add('hidden');
  }, 1900);
});

/* ===== CUSTOM CURSOR ===== */
const cursor = document.querySelector('.cursor');
const follower = document.querySelector('.cursor-follower');
if (cursor && follower) {
  document.addEventListener('mousemove', e => {
    cursor.style.left = e.clientX + 'px';
    cursor.style.top = e.clientY + 'px';
    setTimeout(() => {
      follower.style.left = e.clientX + 'px';
      follower.style.top = e.clientY + 'px';
    }, 80);
  });
  document.querySelectorAll('a, button, .product-card, .ingredient-item').forEach(el => {
    el.addEventListener('mouseenter', () => { cursor.classList.add('hovered'); follower.classList.add('hovered'); });
    el.addEventListener('mouseleave', () => { cursor.classList.remove('hovered'); follower.classList.remove('hovered'); });
  });
}

/* ===== NAVBAR SCROLL ===== */
const navbar = document.querySelector('.navbar');
if (navbar) {
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
  });
}

/* ===== MOBILE MENU ===== */
const hamburger = document.querySelector('.nav-hamburger');
const mobileNav = document.querySelector('.mobile-nav');
const mobileClose = document.querySelector('.mobile-nav-close');
if (hamburger && mobileNav) {
  hamburger.addEventListener('click', () => mobileNav.classList.add('open'));
  if (mobileClose) mobileClose.addEventListener('click', () => mobileNav.classList.remove('open'));
  mobileNav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => mobileNav.classList.remove('open')));
}

/* ===== SCROLL REVEAL ===== */
const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('active');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
revealEls.forEach(el => revealObserver.observe(el));

/* ===== HERO PARTICLES ===== */
const particlesContainer = document.querySelector('.hero-particles');
if (particlesContainer) {
  for (let i = 0; i < 14; i++) {
    const p = document.createElement('div');
    p.classList.add('particle');
    const size = Math.random() * 5 + 2;
    p.style.cssText = `width:${size}px;height:${size}px;left:${Math.random()*100}%;animation-duration:${Math.random()*12+10}s;animation-delay:${Math.random()*8}s;`;
    particlesContainer.appendChild(p);
  }
}

/* ===== COUNTER ANIMATION ===== */
function animateCounter(el) {
  const target = parseInt(el.dataset.target, 10);
  const step = target / (1800 / 16);
  let current = 0;
  const timer = setInterval(() => {
    current += step;
    if (current >= target) { current = target; clearInterval(timer); }
    el.textContent = Math.floor(current) + (el.dataset.suffix || '');
  }, 16);
}
const counterEls = document.querySelectorAll('[data-target]');
if (counterEls.length) {
  const counterObs = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) { animateCounter(entry.target); counterObs.unobserve(entry.target); }
    });
  }, { threshold: 0.5 });
  counterEls.forEach(el => counterObs.observe(el));
}

/* ===== ACTIVE NAV LINK ===== */
const currentPage = window.location.pathname.split('/').pop() || 'index.html';
document.querySelectorAll('.nav-links a, .mobile-nav a').forEach(a => {
  const href = a.getAttribute('href');
  if (href === currentPage || (currentPage === '' && href === 'index.html')) a.classList.add('active');
});

/* ===== SMOOTH MARQUEE DUPLICATE ===== */
const track = document.querySelector('.marquee-track');
if (track) track.innerHTML += track.innerHTML;

/* ===== CONTACT FORM ===== */
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', e => {
    e.preventDefault();
    const btn = contactForm.querySelector('.form-submit');
    btn.textContent = 'Message Sent! ✓';
    btn.style.background = '#2D6A4F';
    setTimeout(() => { btn.textContent = 'Send Message →'; btn.style.background = ''; contactForm.reset(); }, 3000);
  });
}

/* ===== PRODUCT FILTER ===== */
const filterBtns = document.querySelectorAll('.filter-btn');
const productItems = document.querySelectorAll('.product-item');
if (filterBtns.length) {
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;
      productItems.forEach(item => {
        const show = filter === 'all' || item.dataset.category === filter;
        item.style.opacity = show ? '1' : '0.3';
        item.style.transform = show ? 'scale(1)' : 'scale(0.95)';
        item.style.pointerEvents = show ? 'all' : 'none';
      });
    });
  });
}

/* ===== PARALLAX ON HERO ===== */
const heroSection = document.querySelector('.hero');
if (heroSection) {
  window.addEventListener('scroll', () => {
    const heroBg = heroSection.querySelector('.hero-bg');
    if (heroBg && window.scrollY < window.innerHeight) heroBg.style.transform = `translateY(${window.scrollY * 0.3}px)`;
  });
}

/* ===== CTA NEWSLETTER ===== */
const ctaForm = document.querySelector('.cta-form');
if (ctaForm) {
  ctaForm.addEventListener('submit', e => {
    e.preventDefault();
    const btn = ctaForm.querySelector('.cta-submit');
    btn.textContent = 'Subscribed! ✓';
    setTimeout(() => { btn.textContent = 'Subscribe'; ctaForm.reset(); }, 3000);
  });
}

/* ============================================================
   CART SYSTEM
   ============================================================ */
const CART_KEY = 'noeCart';

function getCart() {
  try { return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); } catch { return []; }
}
function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
}
function cartTotal(cart) {
  return cart.reduce((s, i) => s + i.price * i.qty, 0);
}
function cartCount(cart) {
  return cart.reduce((s, i) => s + i.qty, 0);
}

/* Update cart badge across all nav */
function updateCartBadge() {
  const cart = getCart();
  const count = cartCount(cart);
  document.querySelectorAll('.cart-count').forEach(el => {
    el.textContent = count;
    el.classList.toggle('show', count > 0);
  });
}

/* Show toast notification */
function showToast(msg) {
  let toast = document.querySelector('.cart-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.className = 'cart-toast';
    toast.innerHTML = '<span class="toast-icon">🛒</span><span class="toast-msg"></span>';
    document.body.appendChild(toast);
  }
  toast.querySelector('.toast-msg').textContent = msg;
  toast.classList.add('show');
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => toast.classList.remove('show'), 2800);
}

/* Add to cart */
function addToCart(name, price, img) {
  const cart = getCart();
  const existing = cart.find(i => i.name === name);
  if (existing) {
    existing.qty++;
  } else {
    cart.push({ name, price, img, qty: 1 });
  }
  saveCart(cart);
  updateCartBadge();
  showToast(name + ' added to your cart!');
}

/* Wire up all .btn-cart buttons */
function bindAddToCartButtons() {
  document.querySelectorAll('.btn-cart').forEach(btn => {
    btn.addEventListener('click', function(e) {
      const name  = btn.dataset.name;
      const price = parseInt(btn.dataset.price, 10);
      const img   = btn.dataset.img || '';
      addToCart(name, price, img);

      /* Button "Added" feedback */
      const orig = btn.innerHTML;
      btn.innerHTML = '✓ Added!';
      btn.classList.add('added');
      setTimeout(() => {
        btn.innerHTML = orig;
        btn.classList.remove('added');
      }, 1500);

      /* Fly-dot animation toward cart icon */
      const cartIcon = document.querySelector('.cart-icon-btn');
      if (cartIcon) {
        const btnRect  = btn.getBoundingClientRect();
        const cartRect = cartIcon.getBoundingClientRect();
        const dot = document.createElement('div');
        dot.className = 'cart-fly-dot';
        const tx = (cartRect.left + cartRect.width / 2) - (btnRect.left + btnRect.width / 2);
        const ty = (cartRect.top  + cartRect.height / 2) - (btnRect.top  + btnRect.height / 2);
        dot.style.left = (btnRect.left + btnRect.width / 2 - 7) + 'px';
        dot.style.top  = (btnRect.top  + btnRect.height / 2 - 7) + 'px';
        dot.style.setProperty('--tx', tx + 'px');
        dot.style.setProperty('--ty', ty + 'px');
        document.body.appendChild(dot);
        setTimeout(() => dot.remove(), 600);
      }

      /* Badge bounce */
      document.querySelectorAll('.cart-count').forEach(el => {
        el.classList.remove('bounce');
        void el.offsetWidth;
        el.classList.add('bounce');
      });
    });
  });
}

/* ===== CART PAGE RENDERING ===== */
function renderCartPage() {
  const container = document.getElementById('cartItemsContainer');
  if (!container) return;
  const cart = getCart();

  if (cart.length === 0) {
    container.innerHTML = `
      <div class="cart-empty">
        <div class="cart-empty-icon">🛒</div>
        <h3>Your Cart is Empty</h3>
        <p>No items in your cart yet. Browse our products to get started.</p>
        <a href="products.html" class="btn-primary" style="display:inline-flex;margin:0 auto;">Browse Products <span class="arrow">→</span></a>
      </div>`;
    document.getElementById('cartSummaryWrap').style.display = 'none';
    return;
  }

  document.getElementById('cartSummaryWrap').style.display = 'block';

  const rows = cart.map((item, idx) => `
    <div class="cart-item" data-idx="${idx}">
      <div class="cart-item-info">
        <img src="${item.img}" alt="${item.name}" class="cart-item-img" onerror="this.src='images/logo.png'">
        <div>
          <div class="cart-item-name">${item.name}</div>
          <div class="cart-item-sub">Unit Price: Rs ${item.price.toLocaleString()}</div>
        </div>
      </div>
      <div class="qty-control">
        <button class="qty-btn" onclick="changeQty(${idx},-1)">−</button>
        <span class="qty-num">${item.qty}</span>
        <button class="qty-btn" onclick="changeQty(${idx},1)">+</button>
      </div>
      <div class="item-price">Rs ${(item.price * item.qty).toLocaleString()}</div>
      <button class="item-remove" onclick="removeItem(${idx})" title="Remove">✕</button>
    </div>`).join('');

  container.innerHTML = `
    <div class="cart-items-header">
      <span>Product</span><span>Qty</span><span>Total</span><span></span>
    </div>
    ${rows}`;

  const total = cartTotal(cart);
  const delivery = total >= 5000 ? 0 : 250;
  document.getElementById('cartSubtotal').textContent  = 'Rs ' + total.toLocaleString();
  document.getElementById('cartDelivery').textContent  = delivery === 0 ? 'Free' : 'Rs ' + delivery;
  document.getElementById('cartTotal').textContent     = 'Rs ' + (total + delivery).toLocaleString();
}

window.changeQty = function(idx, delta) {
  const cart = getCart();
  cart[idx].qty = Math.max(1, cart[idx].qty + delta);
  saveCart(cart);
  updateCartBadge();
  renderCartPage();
};
window.removeItem = function(idx) {
  const cart = getCart();
  cart.splice(idx, 1);
  saveCart(cart);
  updateCartBadge();
  renderCartPage();
};

/* ===== CHECKOUT PAGE ===== */
function renderCheckoutSummary() {
  const list = document.getElementById('checkoutItemsList');
  if (!list) return;
  const cart = getCart();

  if (cart.length === 0) {
    window.location.href = 'cart.html';
    return;
  }

  list.innerHTML = cart.map(item => `
    <div class="co-item">
      <img src="${item.img}" alt="${item.name}" class="co-item-img" onerror="this.src='images/logo.png'">
      <div class="co-item-details">
        <div class="co-item-name">${item.name}</div>
        <div class="co-item-qty">x${item.qty}</div>
      </div>
      <div class="co-item-price">Rs ${(item.price * item.qty).toLocaleString()}</div>
    </div>`).join('');

  const total    = cartTotal(cart);
  const delivery = total >= 5000 ? 0 : 250;
  document.getElementById('coSubtotal').textContent  = 'Rs ' + total.toLocaleString();
  document.getElementById('coDelivery').textContent  = delivery === 0 ? 'Free' : 'Rs ' + delivery;
  document.getElementById('coTotal').textContent     = 'Rs ' + (total + delivery).toLocaleString();
}

/* Payment method toggle */
function initPaymentToggle() {
  document.querySelectorAll('.payment-option').forEach(radio => {
    radio.addEventListener('change', () => {
      document.querySelectorAll('.payment-detail-box').forEach(b => b.classList.remove('show'));
      const box = document.getElementById('detail-' + radio.value);
      if (box) box.classList.add('show');
    });
  });
}

/* Place order */
function initPlaceOrder() {
  const form = document.getElementById('checkoutForm');
  if (!form) return;
  form.addEventListener('submit', e => {
    e.preventDefault();
    const payMethod = document.querySelector('.payment-option:checked');
    if (!payMethod) { alert('Meherbani kar ke payment method select karein.'); return; }

    const cart    = getCart();
    const total   = cartTotal(cart);
    const delivery = total >= 5000 ? 0 : 250;
    const name    = form.querySelector('[name="fullName"]').value;
    const phone   = form.querySelector('[name="phone"]').value;
    const city    = form.querySelector('[name="city"]').value;
    const address = form.querySelector('[name="address"]').value;
    const method  = payMethod.value;

    const items = cart.map(i => `${i.name} x${i.qty} = Rs ${(i.price*i.qty).toLocaleString()}`).join('%0A');
    const waMsg = `Assalam o Alaikum!%0A%0ANew Order:%0A${items}%0A%0ADelivery: Rs ${delivery}%0ATotal: Rs ${(total+delivery).toLocaleString()}%0A%0ACustomer: ${name}%0APhone: ${phone}%0ACity: ${city}%0AAddress: ${address}%0APayment: ${method}`;

    saveCart([]);
    updateCartBadge();

    const successDiv = document.getElementById('orderSuccess');
    const formWrap   = document.getElementById('checkoutFormWrap');
    if (successDiv && formWrap) {
      formWrap.style.display = 'none';
      successDiv.classList.add('show');
      document.getElementById('successPhone').textContent = phone;
    }

    setTimeout(() => {
      window.open(`https://wa.me/923211112280?text=${waMsg}`, '_blank');
    }, 1200);
  });
}

/* ===== INIT ON DOM READY ===== */
document.addEventListener('DOMContentLoaded', () => {
  updateCartBadge();
  bindAddToCartButtons();
  renderCartPage();
  renderCheckoutSummary();
  initPaymentToggle();
  initPlaceOrder();
});

/* ===== CUSTOM VIDEO PLAYER ===== */
(function() {
  function fmt(s) {
    s = Math.floor(s || 0);
    return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
  }

  function initVideo(video) {
    var wrap = video.parentElement;
    if (!wrap || wrap.querySelector('.vid-controls')) return;
    wrap.classList.add('vid-outer');
    wrap.style.position = 'relative';

    // Big centre play button
    var bigBtn = document.createElement('div');
    bigBtn.className = 'vid-play-btn';
    bigBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>';
    wrap.appendChild(bigBtn);

    // Bottom control bar
    var bar = document.createElement('div');
    bar.className = 'vid-controls';
    bar.innerHTML =
      '<button class="vid-pp" aria-label="Play">' +
        '<svg class="vi-play" viewBox="0 0 24 24" width="13" height="13" fill="white"><path d="M8 5v14l11-7z"/></svg>' +
        '<svg class="vi-pause" viewBox="0 0 24 24" width="13" height="13" fill="white" style="display:none"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>' +
      '</button>' +
      '<div class="vid-progress"><div class="vid-fill"></div></div>' +
      '<span class="vid-time">0:00 / 0:00</span>';
    wrap.appendChild(bar);

    var ppBtn  = bar.querySelector('.vid-pp');
    var prog   = bar.querySelector('.vid-progress');
    var fill   = bar.querySelector('.vid-fill');
    var timeEl = bar.querySelector('.vid-time');
    var iPlay  = ppBtn.querySelector('.vi-play');
    var iPause = ppBtn.querySelector('.vi-pause');

    function refresh() {
      var paused = video.paused;
      bigBtn.style.opacity = paused ? '1' : '0';
      bigBtn.style.pointerEvents = paused ? 'all' : 'none';
      wrap.classList.toggle('paused', paused);
      iPlay.style.display  = paused  ? 'block' : 'none';
      iPause.style.display = !paused ? 'block' : 'none';
      var pct = video.duration ? (video.currentTime / video.duration * 100) : 0;
      fill.style.width = pct + '%';
      timeEl.textContent = fmt(video.currentTime) + ' / ' + fmt(video.duration);
    }

    function seek(e) {
      if (!video.duration) return;
      var rect = prog.getBoundingClientRect();
      var x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
      video.currentTime = Math.max(0, Math.min(1, x / rect.width)) * video.duration;
    }

    bigBtn.addEventListener('click', function(e) { e.stopPropagation(); video.play(); });
    ppBtn.addEventListener('click',  function(e) { e.stopPropagation(); video.paused ? video.play() : video.pause(); });
    video.addEventListener('click', function() { video.paused ? video.play() : video.pause(); });
    video.addEventListener('play',        refresh);
    video.addEventListener('pause',       refresh);
    video.addEventListener('timeupdate',  refresh);
    video.addEventListener('loadedmetadata', refresh);

    var dragging = false;
    prog.addEventListener('click', seek);
    prog.addEventListener('mousedown', function(e) { dragging = true; seek(e); e.preventDefault(); });
    document.addEventListener('mousemove', function(e) { if (dragging) seek(e); });
    document.addEventListener('mouseup',   function()  { dragging = false; });
    prog.addEventListener('touchstart', function(e) { e.preventDefault(); seek(e); }, { passive: false });
    prog.addEventListener('touchmove',  function(e) { e.preventDefault(); seek(e); }, { passive: false });

    video.removeAttribute('controls');
    wrap.classList.add('paused');
    refresh();
  }

  document.querySelectorAll('video').forEach(initVideo);
})();

/* ===== REVIEWS SLIDER ===== */
(function() {
  function initSlider(track, prevBtn, nextBtn, gap, autoDelay) {
    var cards = Array.from(track.children);
    var n = cards.length;
    var cur = 0, autoTimer;

    function pv() {
      var w = window.innerWidth;
      if (w <= 768) return 1;
      if (w <= 1024) return Math.min(2, n);
      return Math.min(3, n);
    }

    function maxIdx() { return Math.max(0, n - pv()); }

    function setWidths() {
      var p = pv(), g = gap;
      cards.forEach(function(c) {
        c.style.flex = '0 0 calc(' + (100 / p) + '% - ' + (g * (p - 1) / p) + 'px)';
      });
    }

    function goTo(idx) {
      cur = ((idx % n) + n) % n;
      if (cur > maxIdx()) cur = 0;
      var w = cards[0].getBoundingClientRect().width + gap;
      track.style.transform = 'translateX(-' + (cur * w) + 'px)';
    }

    function startAuto() {
      autoTimer = setInterval(function() { goTo(cur + 1); }, autoDelay);
    }

    function resetAuto() { clearInterval(autoTimer); startAuto(); }

    if (prevBtn) prevBtn.addEventListener('click', function() { goTo(cur - 1); resetAuto(); });
    if (nextBtn) nextBtn.addEventListener('click', function() { goTo(cur + 1); resetAuto(); });

    var sx = 0;
    track.addEventListener('touchstart', function(e) { sx = e.touches[0].clientX; clearInterval(autoTimer); }, { passive: true });
    track.addEventListener('touchend', function(e) {
      var dx = e.changedTouches[0].clientX - sx;
      if (Math.abs(dx) > 48) goTo(cur + (dx < 0 ? 1 : -1));
      startAuto();
    });

    window.addEventListener('resize', function() { setWidths(); cur = 0; goTo(0); });
    setWidths();
    goTo(0);
    startAuto();
  }

  // Home page — already has HTML slider structure
  var homeTrack = document.querySelector('.rev-track');
  if (homeTrack) {
    initSlider(homeTrack, document.querySelector('.rev-prev'), document.querySelector('.rev-next'), 24, 4000);
  }

  // Product pages / reviews page — convert .reviews-grid to slider
  document.querySelectorAll('.reviews-grid').forEach(function(grid) {
    var cards = Array.from(grid.querySelectorAll('.review-card'));
    if (cards.length < 2) return;

    var wrap   = document.createElement('div');  wrap.className = 'rs-wrap';
    var slider = document.createElement('div');  slider.className = 'rs-slider';
    var track  = document.createElement('div');  track.className = 'rs-track';

    var prevBtn = document.createElement('button');
    prevBtn.className = 'rs-nav-btn rs-prev';
    prevBtn.innerHTML = '&#8249;';
    prevBtn.setAttribute('aria-label', 'Previous review');

    var nextBtn = document.createElement('button');
    nextBtn.className = 'rs-nav-btn rs-next';
    nextBtn.innerHTML = '&#8250;';
    nextBtn.setAttribute('aria-label', 'Next review');

    cards.forEach(function(c) {
      c.classList.remove('reveal', 'reveal-left', 'reveal-right', 'reveal-delay-1', 'reveal-delay-2', 'reveal-delay-3');
      track.appendChild(c);
    });

    slider.appendChild(track);
    wrap.appendChild(prevBtn);
    wrap.appendChild(slider);
    wrap.appendChild(nextBtn);
    grid.parentNode.replaceChild(wrap, grid);

    initSlider(track, prevBtn, nextBtn, 20, 4000);
  });
})();