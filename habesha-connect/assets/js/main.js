/* =============================================
   Habesha Connect - Main JavaScript
   ============================================= */

'use strict';

// =============================================
// LIKE BUTTON
// =============================================
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-like');
    if (!btn) return;
    e.preventDefault();

    const userId = btn.dataset.userId;
    if (!userId) return;

    btn.disabled = true;

    fetch('/habesha-connect/api/like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `user_id=${userId}&csrf=${document.querySelector('meta[name="csrf"]')?.content ?? ''}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.classList.toggle('liked', data.action === 'liked');
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = data.action === 'liked' ? 'bi bi-heart-fill' : 'bi bi-heart';
            }
            if (data.is_match) showMatchModal(data.match_user);
        }
    })
    .catch(() => {})
    .finally(() => { btn.disabled = false; });
});

// =============================================
// MATCH MODAL
// =============================================
function showMatchModal(user) {
    const modal = document.createElement('div');
    modal.innerHTML = `
    <div class="modal fade" tabindex="-1" id="matchModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
          <div class="modal-body text-center p-5" style="background:linear-gradient(135deg,#1a6e3c,#13512c)">
            <div class="mb-3" style="font-size:3.5rem">💛</div>
            <h3 class="text-white fw-800 mb-2">It's a Match!</h3>
            <p class="text-white-75 mb-4">You and <strong>${user?.username ?? 'someone'}</strong> liked each other!</p>
            <div class="d-flex gap-3 justify-content-center">
              <a href="/habesha-connect/messages.php?user=${user?.id ?? ''}" class="btn btn-warning fw-700 px-4">Send Message</a>
              <button class="btn btn-outline-light fw-600 px-4" data-bs-dismiss="modal">Keep Browsing</button>
            </div>
          </div>
        </div>
      </div>
    </div>`;
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal.querySelector('#matchModal'));
    bsModal.show();
    modal.querySelector('#matchModal').addEventListener('hidden.bs.modal', () => modal.remove());
}

// =============================================
// PASSWORD STRENGTH
// =============================================
const pwdInput = document.getElementById('password');
const pwdBar   = document.getElementById('passwordStrength');
const pwdText  = document.getElementById('passwordStrengthText');

if (pwdInput && pwdBar) {
    pwdInput.addEventListener('input', function() {
        const strength = calcPasswordStrength(this.value);
        pwdBar.className = `password-strength strength-${strength.score}`;
        if (pwdText) {
            pwdText.textContent = strength.label;
            pwdText.style.color = strength.color;
        }
    });
}

function calcPasswordStrength(pwd) {
    let score = 0;
    if (!pwd) return { score: 0, label: '', color: '' };
    if (pwd.length >= 8)  score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['', '#ef4444', '#f59e0b', '#22c55e', '#16a34a'];
    return { score, label: labels[score], color: colors[score] };
}

// =============================================
// PHOTO PREVIEW
// =============================================
document.addEventListener('change', function(e) {
    const input = e.target.closest('input[type="file"][data-preview]');
    if (!input) return;
    const preview = document.getElementById(input.dataset.preview);
    if (!preview || !input.files[0]) return;

    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        showToast('Photo must be under 5MB', 'danger');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        preview.src = e.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
});

// =============================================
// CHAT / MESSAGING
// =============================================
const chatForm    = document.getElementById('chatForm');
const chatMessages = document.getElementById('chatMessages');

if (chatForm) {
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const input   = this.querySelector('input[name="message"]');
        const msg     = input.value.trim();
        const toUser  = this.querySelector('input[name="to_user"]')?.value;
        if (!msg || !toUser) return;

        const body = new URLSearchParams({ message: msg, to_user_id: toUser, csrf: document.querySelector('meta[name="csrf"]')?.content ?? '' });
        fetch('/habesha-connect/api/message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                appendMessage(msg, 'mine', 'Just now');
                input.value = '';
                scrollChatBottom();
            }
        })
        .catch(() => {});
    });
}

function appendMessage(text, side, time) {
    if (!chatMessages) return;
    const wrap = document.createElement('div');
    wrap.className = `message-bubble-wrap ${side}`;
    wrap.innerHTML = `
        <div class="message-bubble ${side}">${escapeHtml(text)}</div>
        <div class="message-time">${time}</div>`;
    chatMessages.appendChild(wrap);
    scrollChatBottom();
}

function scrollChatBottom() {
    if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Poll for new messages every 5s
let lastMsgId = 0;
const chatPartner = document.querySelector('[data-partner-id]')?.dataset.partnerId;

if (chatPartner && chatMessages) {
    scrollChatBottom();
    setInterval(() => {
        fetch(`/habesha-connect/api/get-messages.php?partner_id=${chatPartner}&last_id=${lastMsgId}`)
            .then(r => r.json())
            .then(data => {
                if (data.messages && data.messages.length) {
                    data.messages.forEach(m => {
                        appendMessage(m.message, 'theirs', m.time);
                        lastMsgId = Math.max(lastMsgId, m.id);
                    });
                }
            })
            .catch(() => {});
    }, 5000);
}

// =============================================
// TOAST NOTIFICATIONS
// =============================================
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer') || createToastContainer();
    const toast = document.createElement('div');
    const icons = { success: 'check-circle-fill', danger: 'x-circle-fill', warning: 'exclamation-circle-fill', info: 'info-circle-fill' };
    toast.className = `toast align-items-center text-bg-${type} border-0 mb-2`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-${icons[type] || 'info-circle-fill'}"></i> ${escapeHtml(message)}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;
    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 4000 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function createToastContainer() {
    const el = document.createElement('div');
    el.id = 'toastContainer';
    el.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    el.style.zIndex = '9999';
    document.body.appendChild(el);
    return el;
}

// =============================================
// UTILS
// =============================================
function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// Age range slider sync
const ageMin = document.getElementById('ageMin');
const ageMax = document.getElementById('ageMax');
const ageMinVal = document.getElementById('ageMinVal');
const ageMaxVal = document.getElementById('ageMaxVal');

if (ageMin && ageMax) {
    ageMin.addEventListener('input', () => {
        if (parseInt(ageMin.value) > parseInt(ageMax.value)) ageMin.value = ageMax.value;
        if (ageMinVal) ageMinVal.textContent = ageMin.value;
    });
    ageMax.addEventListener('input', () => {
        if (parseInt(ageMax.value) < parseInt(ageMin.value)) ageMax.value = ageMin.value;
        if (ageMaxVal) ageMaxVal.textContent = ageMax.value;
    });
}

// Dismiss alerts auto
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        if (bsAlert) bsAlert.close();
    }, 6000);
});

// Navbar shrink on scroll
const mainNav = document.getElementById('mainNav');
if (mainNav) {
    window.addEventListener('scroll', () => {
        mainNav.classList.toggle('nav-scrolled', window.scrollY > 40);
    }, { passive: true });
}

// Animate counters
document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count);
    let count = 0;
    const step = Math.max(1, Math.floor(target / 60));
    const timer = setInterval(() => {
        count = Math.min(count + step, target);
        el.textContent = count.toLocaleString();
        if (count >= target) clearInterval(timer);
    }, 30);
});
