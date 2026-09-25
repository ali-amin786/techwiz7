(() => {
  /* Mobile offcanvas is powered by Bootstrap 5. This file keeps hamburger focus after close. */
  const offcanvas = document.getElementById('sidebarOffcanvas');
  if (!offcanvas) return;
  offcanvas.addEventListener('hidden.bs.offcanvas', () => {
    const trigger = document.querySelector('[data-bs-target="#sidebarOffcanvas"]');
    trigger?.focus();
  });
})();
