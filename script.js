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
  for (let i = 0; i < 18; i++) {
    const p = document.createElement('div');
    p.classList.add('particle');
    const size = Math.random() * 6 + 3;
    p.style.cssText = `
      width:${size}px; height:${size}px;
      left:${Math.random() * 100}%;
      animation-duration:${Math.random() * 12 + 10}s;
      animation-delay:${Math.random() * 8}s;
    `;
    particlesContainer.appendChild(p);
  }
}

/* ===== COUNTER ANIMATION ===== */
function animateCounter(el) {
  const target = parseInt(el.dataset.target, 10);
  const duration = 1800;
  const step = target / (duration / 16);
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
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        counterObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });
  counterEls.forEach(el => counterObs.observe(el));
}

/* ===== ACTIVE NAV LINK ===== */
const currentPage = window.location.pathname.split('/').pop() || 'index.html';
document.querySelectorAll('.nav-links a, .mobile-nav a').forEach(a => {
  const href = a.getAttribute('href');
  if (href === currentPage || (currentPage === '' && href === 'index.html')) {
    a.classList.add('active');
  }
});

/* ===== SMOOTH MARQUEE DUPLICATE ===== */
const track = document.querySelector('.marquee-track');
if (track) {
  track.innerHTML += track.innerHTML;
}

/* ===== CONTACT FORM ===== */
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', e => {
    e.preventDefault();
    const btn = contactForm.querySelector('.form-submit');
    btn.textContent = 'Message Sent! ✓';
    btn.style.background = '#2D6A4F';
    setTimeout(() => {
      btn.textContent = 'Send Message';
      btn.style.background = '';
      contactForm.reset();
    }, 3000);
  });
}

/* ===== PRODUCT FILTER (products page) ===== */
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
    const scrolled = window.scrollY;
    const heroBg = heroSection.querySelector('.hero-bg');
    if (heroBg && scrolled < window.innerHeight) {
      heroBg.style.transform = `translateY(${scrolled * 0.3}px)`;
    }
  });
}

/* ===== CTA NEWSLETTER ===== */
const ctaForm = document.querySelector('.cta-form');
if (ctaForm) {
  ctaForm.addEventListener('submit', e => {
    e.preventDefault();
    const btn = ctaForm.querySelector('.cta-submit');
    btn.textContent = 'Subscribed! ✓';
    setTimeout(() => {
      btn.textContent = 'Subscribe';
      ctaForm.reset();
    }, 3000);
  });
}
