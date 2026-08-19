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
  showToast(name + ' cart mein add ho gaya!');
}

/* Wire up all .btn-cart buttons */
function bindAddToCartButtons() {
  document.querySelectorAll('.btn-cart').forEach(btn => {
    btn.addEventListener('click', () => {
      const name  = btn.dataset.name;
      const price = parseInt(btn.dataset.price, 10);
      const img   = btn.dataset.img || '';
      addToCart(name, price, img);
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
        <h3>Aapka Cart Khali Hai</h3>
        <p>Abhi koi product cart mein nahi hai. Hamare products browse karein.</p>
        <a href="products.html" class="btn-primary" style="display:inline-flex;margin:0 auto;">Products Dekhein <span class="arrow">→</span></a>
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
  const delivery = total >= 1500 ? 0 : 150;
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
  const delivery = total >= 1500 ? 0 : 150;
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
    const delivery = total >= 1500 ? 0 : 150;
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
      window.open(`https://wa.me/923335222865?text=${waMsg}`, '_blank');
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
