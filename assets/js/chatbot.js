(() => {
  const toggle = document.getElementById('chatbotToggle');
  const panel = document.getElementById('chatbotPanel');
  const form = document.getElementById('chatbotForm');
  const log = document.getElementById('chatbotLog');
  if (!toggle || !panel) return;

  toggle.addEventListener('click', () => {
    panel.classList.toggle('d-none');
    toggle.setAttribute('aria-expanded', String(!panel.classList.contains('d-none')));
  });

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const input = document.getElementById('chatbotInput');
    const message = (input?.value || '').trim();
    if (!message) return;
    if (log) {
      log.insertAdjacentHTML('beforeend', `<div><strong>You:</strong> ${message.replace(/</g, '&lt;')}</div>`);
    }
    input.value = '';
    try {
      const body = new FormData(form);
      body.set('message', message);
      const res = await fetch('chatbot-response.php', { method: 'POST', body });
      const data = await res.json();
      if (log) {
        log.insertAdjacentHTML('beforeend', `<div><strong>Bot:</strong> ${(data.response || 'Coming in Section B.').replace(/</g, '&lt;')}</div>`);
      }
    } catch (err) {
      if (log) {
        log.insertAdjacentHTML('beforeend', '<div><strong>Bot:</strong> FAQ engine will be wired in Section B.</div>');
      }
    }
  });
})();
