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
        const isSaved = data.status === 'saved';
        btn.classList.toggle('is-saved', isSaved);
        btn.classList.toggle('btn-accent', isSaved);
        btn.classList.toggle('btn-ghost', !isSaved);
        btn.textContent = isSaved ? '★ Saved' : '☆ Save';

        // Update sidebar saved count if element exists
        const countSpan = document.getElementById('sidebarSavedCount');
        if (countSpan && data.total_bookmarks !== undefined) {
          countSpan.textContent = data.total_bookmarks;
        }
      } catch (err) {
        console.error('Bookmark error:', err);
      }
    });
  });

  // Interactive Star Rating Submission
  document.querySelectorAll('.star-rating input').forEach((radio) => {
    radio.addEventListener('change', async (e) => {
      if (document.body.dataset.auth !== '1') {
        guestModal?.show();
        e.preventDefault();
        return;
      }
      const form = radio.closest('form') || radio.closest('.rating-container');
      const postId = form?.dataset.postId || radio.name.replace('rating_', '');
      const stars = radio.value;
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="csrf_token"]')?.value;

      try {
        const res = await fetch('rate-media.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            post_id: postId,
            rating_stars: stars,
            csrf_token: csrf || ''
          }),
        });
        const data = await res.json();
        if (data.status === 'success') {
          const avgLabel = document.getElementById(`avgRating_${postId}`);
          if (avgLabel) {
            avgLabel.textContent = `${data.avg_rating} / 5 (${data.total_ratings} votes)`;
          }
          const userLabel = document.getElementById(`userRatingFeedback_${postId}`);
          if (userLabel) {
            userLabel.textContent = `You rated this ${stars} ★`;
            userLabel.classList.remove('d-none');
          }
        }
      } catch (err) {
        console.error('Rating error:', err);
      }
    });
  });
})();
