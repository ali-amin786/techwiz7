(() => {
  const setCookie = (name, value) => {
    document.cookie = `${name}=${encodeURIComponent(value)};path=/;max-age=31536000;SameSite=Lax`;
  };

  const themeBtn = document.getElementById('themeToggle');
  if (themeBtn) {
    themeBtn.addEventListener('click', () => {
      const next = document.documentElement.classList.contains('theme-light') ? 'dark' : 'light';
      document.documentElement.classList.toggle('theme-light', next === 'light');
      document.documentElement.classList.toggle('theme-dark', next === 'dark');
      setCookie('fh_theme', next);
      themeBtn.textContent = next === 'light' ? 'Dark' : 'Light';
    });
  }

  const fontBtn = document.getElementById('fontToggle');
  if (fontBtn) {
    const cycle = { sm: 'md', md: 'lg', lg: 'sm' };
    fontBtn.addEventListener('click', () => {
      const current = fontBtn.dataset.font || 'md';
      const next = cycle[current] || 'md';
      document.documentElement.classList.remove('font-sm', 'font-md', 'font-lg');
      document.documentElement.classList.add(`font-${next}`);
      fontBtn.dataset.font = next;
      setCookie('fh_font', next);
    });
  }

  const guestModalEl = document.getElementById('guestAuthModal');
  const guestModal = guestModalEl && window.bootstrap ? new bootstrap.Modal(guestModalEl) : null;

  document.querySelectorAll('.js-guest-guard, [data-requires-auth]').forEach((el) => {
    el.addEventListener('click', (event) => {
      if (document.body.dataset.auth === '1') {
        return;
      }
      event.preventDefault();
      if (guestModal) {
        guestModal.show();
      } else {
        window.location.href = el.getAttribute('href') || 'login.php';
      }
    });
  });

  document.getElementById('a11ySettings')?.addEventListener('click', () => {
    themeBtn?.focus();
  });

  document.querySelectorAll('.js-bookmark').forEach((btn) => {
    btn.addEventListener('click', async () => {
      if (document.body.dataset.auth !== '1') {
        guestModal?.show();
        return;
      }
      const postId = btn.dataset.postId;
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="csrf_token"]')?.value;
      try {
        const res = await fetch('save-bookmark.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ post_id: postId, csrf_token: csrf || '' }),
        });
        if (!res.ok) return;
        const data = await res.json();
        btn.classList.toggle('is-saved', data.status === 'saved');
        btn.textContent = data.status === 'saved' ? 'Saved' : 'Save';
      } catch (err) {
        /* endpoint lands in Section B */
      }
    });
  });
})();
