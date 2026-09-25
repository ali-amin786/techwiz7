    </main>
    <footer class="site-footer">
        <div class="footer-sitemap">
            <strong>Sitemap</strong>
            <a href="<?= e(url('index.php')) ?>">Home</a>
            <a href="<?= e(url('explore.php')) ?>">Explore</a>
            <a href="<?= e(url('characters.php')) ?>">Characters</a>
            <a href="<?= e(url('merch.php')) ?>">Merch</a>
            <a href="<?= e(url('upcoming.php')) ?>">Upcoming</a>
            <a href="<?= e(url('events-map.php')) ?>">Events</a>
            <a href="<?= e(url('feedback.php')) ?>">Feedback</a>
            <a href="<?= e(url('login.php')) ?>">Login</a>
            <a href="<?= e(url('register.php')) ?>">Register</a>
        </div>
        <p class="footer-copy">Fan Hub Plus — Multi-Fandom Entertainment Portal</p>
    </footer>
</div>
</div>

<div class="modal fade" id="guestAuthModal" tabindex="-1" aria-labelledby="guestAuthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-modal">
            <div class="modal-header">
                <h2 class="modal-title h5" id="guestAuthModalLabel">Login required</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Is feature ko use karne ke liye Login/Register karein.</p>
                <div class="d-flex gap-2">
                    <a class="btn btn-accent" href="<?= e(url('login.php')) ?>">Login</a>
                    <a class="btn btn-ghost" href="<?= e(url('register.php')) ?>">Register</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="chatbot-widget" id="chatbotWidget" aria-live="polite">
    <button type="button" class="chatbot-toggle" id="chatbotToggle" aria-expanded="false">Chat</button>
    <div class="chatbot-panel d-none" id="chatbotPanel">
        <div class="chatbot-head">FAQ Assistant</div>
        <div class="chatbot-log" id="chatbotLog"></div>
        <form id="chatbotForm" class="chatbot-form">
            <?= csrf_field() ?>
            <input type="text" name="message" id="chatbotInput" class="form-control" placeholder="Ask a question…" autocomplete="off">
        </form>
    </div>
</div>

<script src="<?= e(url('assets/js/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(url('assets/js/offcanvas-nav.js')) ?>"></script>
<script src="<?= e(url('assets/js/main.js')) ?>"></script>
<script src="<?= e(url('assets/js/chatbot.js')) ?>"></script>
<?php if (!empty($include_map_js)): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="<?= e(url('assets/js/map.js')) ?>"></script>
<?php endif; ?>
</body>
</html>
