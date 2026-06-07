// Getas Reality — Main JS

// Navbar scroll effect
window.addEventListener('scroll', function () {
  const nav = document.getElementById('mainNav');
  if (nav) nav.classList.toggle('scrolled', window.scrollY > 50);
});

// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.alert-auto-dismiss').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity 0.5s';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 500);
    }, 4000);
  });
});

// Property image gallery (detail page)
function changeMainImage(src) {
  const main = document.getElementById('mainPropertyImage');
  if (main) {
    main.style.opacity = '0';
    setTimeout(function () {
      main.src = src;
      main.style.transition = 'opacity 0.3s';
      main.style.opacity = '1';
    }, 150);
  }
  document.querySelectorAll('.detail-thumbnails img').forEach(function (img) {
    img.classList.toggle('active', img.src === src);
  });
}

// Admin sidebar mobile toggle
function toggleSidebar() {
  const sidebar = document.getElementById('adminSidebar');
  if (sidebar) sidebar.classList.toggle('show');
}

// Confirm delete
document.addEventListener('click', function (e) {
  const btn = e.target.closest('[data-confirm]');
  if (btn) {
    if (!confirm(btn.dataset.confirm || 'Are you sure?')) {
      e.preventDefault();
    }
  }
});

// Image preview on file select
document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
  input.addEventListener('change', function () {
    const previewId = this.dataset.preview;
    const preview   = document.getElementById(previewId);
    if (!preview || !this.files[0]) return;
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(this.files[0]);
  });
});

// Counter animation (for stats)
function animateCounters() {
  document.querySelectorAll('[data-count]').forEach(function (el) {
    const target = parseInt(el.dataset.count, 10);
    let current  = 0;
    const step   = Math.ceil(target / 50);
    const timer  = setInterval(function () {
      current = Math.min(current + step, target);
      el.textContent = current.toLocaleString();
      if (current >= target) clearInterval(timer);
    }, 30);
  });
}

// Intersection observer for counters
const statsSection = document.querySelector('.hero-stats');
if (statsSection) {
  const obs = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        animateCounters();
        obs.disconnect();
      }
    });
  });
  obs.observe(statsSection);
}

// Form validation visual feedback
document.querySelectorAll('form.needs-validation').forEach(function (form) {
  form.addEventListener('submit', function (e) {
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  });
});
