(function () {

  // ── SIDEBAR ──────────────────────────────────────────
const hamburger     = document.getElementById('hamburger');
const sidebar       = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

function setSidebar(open) {
    if (!sidebar) return;
    sidebar.classList.toggle('is-open', open);
    document.body.classList.toggle('sidebar-open', open);
    sidebar.setAttribute('aria-hidden', String(!open));
    if (hamburger) hamburger.setAttribute('aria-expanded', String(open));
    if (sidebarOverlay) sidebarOverlay.hidden = !open;
}

setSidebar(false);

if (hamburger) {
    hamburger.addEventListener('click', (e) => {
e.preventDefault();
setSidebar(!sidebar.classList.contains('is-open'));
    });
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => setSidebar(false));
}

  // ── MODAL ─────────────────────────────────────────────
document.body.classList.remove('modal-open');

const modal          = document.getElementById('usersModal');
const modalBackdrop  = document.getElementById('usersModalBackdrop');
const modalOpenBtn   = document.getElementById('usersModalBtn');
const modalCloseBtn  = document.getElementById('usersModalClose');

function openModal() {
    if (!modal) return;
    modal.hidden         = false;
    modalBackdrop.hidden = false;

    requestAnimationFrame(() => {
modal.classList.add('is-open');
modalBackdrop.classList.add('is-open');
    });

    document.body.classList.add('modal-open');
    if (modalOpenBtn) modalOpenBtn.setAttribute('aria-expanded', 'true');
    if (modalCloseBtn) modalCloseBtn.focus();
}

function closeModal() {
    if (!modal) return;
    modal.classList.remove('is-open');
    modalBackdrop.classList.remove('is-open');

    modal.addEventListener('transitionend', () => {
modal.hidde   = true;
modalBackdrop.hidden = true;
    }, { once: true });

    document.body.classList.remove('modal-open');
    if (modalOpenBtn) {
modalOpenBtn.setAttribute('aria-expanded', 'false');
modalOpenBtn.focus();
    }
}

if (modalOpenBtn)  modalOpenBtn.addEventListener('click', openModal);
if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);

  // ── GLOBAL KEYDOWN ────────────────────────────────────
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
    if (modal && !modal.hidden) closeModal();
    else setSidebar(false);
    }
});

})();