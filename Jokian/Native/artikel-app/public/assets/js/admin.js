// public/assets/js/admin.js

// ── Sidebar toggle (mobile) ──
const sidebar       = document.getElementById('adminSidebar');
const sidebarToggle = document.getElementById('sidebarToggle');

if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
  });
  // Close sidebar on outside click (mobile)
  document.addEventListener('click', (e) => {
    if (
      sidebar.classList.contains('open') &&
      !sidebar.contains(e.target) &&
      !sidebarToggle.contains(e.target)
    ) {
      sidebar.classList.remove('open');
    }
  });
}

// ── Active sidebar link highlight ──
(function () {
  const links = document.querySelectorAll('.sidebar-link');
  const path  = window.location.pathname;
  links.forEach(link => {
    const href = new URL(link.href, window.location.origin).pathname;
    if (path === href || (href.length > 1 && path.startsWith(href))) {
      link.classList.add('active');
    }
  });
})();

// ── Auto-dismiss flash ──
const flash = document.getElementById('flashMsg');
if (flash) setTimeout(() => flash.style.opacity = '0', 4000);

// ── Delete confirmations ──
document.addEventListener('submit', (e) => {
  const f = e.target;
  if (f.dataset.confirm) {
    if (!confirm(f.dataset.confirm)) e.preventDefault();
  }
});
