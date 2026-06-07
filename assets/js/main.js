/* ===================================================
   HITECHCOMPUTER - Main JavaScript
   =================================================== */

document.addEventListener('DOMContentLoaded', function () {

    // ── AOS Init ──
    AOS.init({ duration: 700, once: true, offset: 80, easing: 'ease-out-cubic' });

    // ── Navbar Scroll ──
    const nav = document.getElementById('mainNav');
    if (nav) {
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    // ── Particles.js ──
    if (document.getElementById('particles-js') && typeof particlesJS !== 'undefined') {
        particlesJS('particles-js', {
            particles: {
                number: { value: 60, density: { enable: true, value_area: 900 } },
                color: { value: ['#00d4ff', '#7b2fff', '#00ff88'] },
                shape: { type: 'circle' },
                opacity: { value: 0.4, random: true, anim: { enable: true, speed: 0.8, opacity_min: 0.1 } },
                size: { value: 2.5, random: true },
                line_linked: { enable: true, distance: 140, color: '#00d4ff', opacity: 0.08, width: 1 },
                move: { enable: true, speed: 0.8, direction: 'none', random: true, out_mode: 'out' }
            },
            interactivity: {
                detect_on: 'canvas',
                events: { onhover: { enable: true, mode: 'grab' }, onclick: { enable: true, mode: 'push' } },
                modes: { grab: { distance: 160, line_linked: { opacity: 0.3 } }, push: { particles_nb: 3 } }
            },
            retina_detect: true
        });
    }

    // ── Counter Animation ──
    const counters = document.querySelectorAll('[data-count]');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(c => counterObserver.observe(c));

    function animateCounter(el) {
        const target = parseInt(el.dataset.count);
        const suffix = el.dataset.suffix || '';
        const duration = 1800;
        const step = target / (duration / 16);
        let current = 0;
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = Math.floor(current) + suffix;
            if (current >= target) clearInterval(timer);
        }, 16);
    }

    // ── AJAX Post Filter ──
    const filterBtns = document.querySelectorAll('.filter-btn');
    const postsContainer = document.getElementById('posts-container');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const category = this.dataset.filter;
            loadPosts(category, getSearchQuery());
        });
    });

    // ── AJAX Search ──
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const activeFilter = document.querySelector('.filter-btn.active');
                const category = activeFilter ? activeFilter.dataset.filter : 'all';
                loadPosts(category, this.value.trim());
            }, 400);
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', function () {
            const activeFilter = document.querySelector('.filter-btn.active');
            const category = activeFilter ? activeFilter.dataset.filter : 'all';
            loadPosts(category, getSearchQuery());
        });
    }

    function getSearchQuery() {
        return searchInput ? searchInput.value.trim() : '';
    }

    // ── Load Posts via AJAX ──
    function loadPosts(category = 'all', search = '') {
        if (!postsContainer) return;
        postsContainer.innerHTML = `<div class="col-12 loading-spinner"><div class="spinner-ring"></div></div>`;

        const params = new URLSearchParams({ category, search });
        fetch(`${SITE_URL}/ajax/load-posts.php?${params}`)
            .then(r => r.text())
            .then(html => {
                postsContainer.innerHTML = html;
                AOS.refresh();
            })
            .catch(() => {
                postsContainer.innerHTML = '<div class="col-12 text-center text-danger py-5">Failed to load posts.</div>';
            });
    }

    // ── Contact Form AJAX ──
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = contactForm.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
            btn.disabled = true;

            const formData = new FormData(contactForm);
            fetch(`${SITE_URL}/ajax/contact-form.php`, { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    showFormAlert(contactForm, data.success, data.message);
                    if (data.success) contactForm.reset();
                })
                .catch(() => showFormAlert(contactForm, false, 'An error occurred. Please try again.'))
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });
    }

    function showFormAlert(form, success, message) {
        let alertEl = form.querySelector('.form-alert');
        if (!alertEl) {
            alertEl = document.createElement('div');
            alertEl.className = 'form-alert alert-glass mt-3';
            form.appendChild(alertEl);
        }
        alertEl.className = `form-alert alert-glass ${success ? 'success' : 'error'} mt-3`;
        alertEl.innerHTML = `<i class="fas ${success ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
        alertEl.style.display = 'flex';
        setTimeout(() => alertEl.remove(), 5000);
    }

    // ── Enrollment Modal AJAX ──
    const enrollForm = document.getElementById('enroll-form');
    if (enrollForm) {
        enrollForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
            btn.disabled = true;

            fetch(`${SITE_URL}/ajax/enroll.php`, { method: 'POST', body: new FormData(this) })
                .then(r => r.json())
                .then(data => {
                    const msgEl = document.getElementById('enroll-message');
                    if (msgEl) {
                        msgEl.className = `alert-glass ${data.success ? 'success' : 'error'} mt-3`;
                        msgEl.textContent = data.message;
                        msgEl.style.display = 'block';
                    }
                    if (data.success) this.reset();
                })
                .finally(() => {
                    btn.innerHTML = 'Submit Enrollment';
                    btn.disabled = false;
                });
        });
    }

    // ── Admin: Delete Post ──
    document.querySelectorAll('.delete-post-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (!confirm('Delete this post? This cannot be undone.')) return;
            const id = this.dataset.id;
            fetch(`${SITE_URL}/ajax/delete-post.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById(`post-row-${id}`);
                        if (row) row.remove();
                    }
                });
        });
    });

    // ── Admin: Toggle Status ──
    document.querySelectorAll('.toggle-status-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            fetch(`${SITE_URL}/ajax/toggle-status.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.querySelector(`#post-row-${id} .status-badge`);
                        if (badge) {
                            badge.textContent = data.new_status;
                            badge.className = `status-badge status-${data.new_status}`;
                        }
                        this.title = data.new_status === 'published' ? 'Unpublish' : 'Publish';
                    }
                });
        });
    });

    // ── Admin: Image Preview ──
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ── Smooth number format ──
    function formatNumber(num) {
        return num >= 1000 ? (num / 1000).toFixed(1) + 'k' : num;
    }

    // ── Typewriter Effect ──
    const typewriterEl = document.getElementById('typewriter');
    if (typewriterEl) {
        const words = ['Mobile Phones', 'Computers', 'Office Machines', 'CCTV Systems'];
        let wordIdx = 0, charIdx = 0, isDeleting = false;

        function type() {
            const word = words[wordIdx];
            typewriterEl.textContent = isDeleting ? word.substring(0, charIdx--) : word.substring(0, charIdx++);
            const speed = isDeleting ? 60 : 100;

            if (!isDeleting && charIdx > word.length) {
                setTimeout(() => { isDeleting = true; }, 1500);
            } else if (isDeleting && charIdx < 0) {
                isDeleting = false;
                wordIdx = (wordIdx + 1) % words.length;
                charIdx = 0;
            }

            setTimeout(type, speed);
        }
        setTimeout(type, 800);
    }

});

// SITE_URL global (set in page)
var SITE_URL = SITE_URL || '';
