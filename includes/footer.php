<!-- ======= Minimal Horizontal Footer ======= -->
<?php date_default_timezone_set('GMT'); ?>
<footer id="footer" class="footer">
  <div class="footer-container">
    <div class="footer-content">
      <div class="footer-brand">
        <span class="footer-logo">SmartVote</span>
        <span class="footer-tagline">Empowering democratic decision making</span>
      </div>
      <div class="footer-meta">
        <span class="copyright">© Copyright SmartVote <?php echo date('Y'); ?>. All Rights Reserved</span>
        <span class="credits">Built by <a href="https://github.com/Aristocratjnr" target="_blank">Obuobi Ayim David</a></span>
        <span class="version">v1.0 <?php echo date('H:i'); ?> GMT</span>
      </div>
    </div>
  </div>
</footer>

<style>
/* Theme Variables - Light Mode (Default) */
:root {
  --footer-bg: #f8f9fa;
  --footer-border: #e9ecef;
  --footer-text: #495057;
  --footer-logo-color: #1a237e;
  --footer-tagline-color: #6c757d;
  --footer-link-color: #0d6efd;
  --footer-link-hover-color: #0a58ca;
}

/* Dark Mode Variables */
[data-bs-theme="dark"] {
  --footer-bg: #212529;
  --footer-border: #495057;
  --footer-text: #adb5bd;
  --footer-logo-color: #6ea8fe;
  --footer-tagline-color: #9aa5b1;
  --footer-link-color: #6ea8fe;
  --footer-link-hover-color: #9ec5fe;
}

/* ======= Minimal Footer Styles ======= */
.footer {
  background: var(--footer-bg);
  border-top: 1px solid var(--footer-border);
  padding: 0.75rem 0;
  font-size: 0.85rem;
  color: var(--footer-text);
  transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.footer-content {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.footer-brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.footer-logo {
  font-weight: 600;
  color: var(--footer-logo-color);
  transition: color 0.3s ease;
}

.footer-tagline {
  color: var(--footer-tagline-color);
  position: relative;
  padding-left: 0.75rem;
  transition: color 0.3s ease;
}

.footer-tagline::before {
  content: "•";
  position: absolute;
  left: 0.25rem;
}

.footer-meta {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.footer-meta span {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.footer a {
  color: var(--footer-link-color);
  text-decoration: none;
  transition: color 0.3s ease;
}

.footer a:hover {
  color: var(--footer-link-hover-color);
  text-decoration: underline;
}

.bi-heart-fill {
  color: #dc3545;
  font-size: 0.9em;
}

@media (max-width: 768px) {
  .footer-content {
    flex-direction: column;
    text-align: center;
    gap: 0.5rem;
  }
  
  .footer-brand {
    flex-direction: column;
    gap: 0;
  }
  
  .footer-tagline::before {
    display: none;
  }
  
  .footer-meta {
    flex-direction: column;
    gap: 0.25rem;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize theme from localStorage
  const currentTheme = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-bs-theme', currentTheme);
  
  // Listen for theme change events
  document.addEventListener('themeChanged', function(e) {
    document.documentElement.setAttribute('data-bs-theme', e.detail.theme);
  });
});
</script>