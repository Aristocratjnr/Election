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
        <span class="credits">Built by <a href="https://github.com/Aristocratjnr" target="_blank">Aristocratjnr</a></span>
        <span class="version">v1.0 <?php echo date('H:i'); ?> GMT</span>
      </div>
    </div>
  </div>
</footer>

<style>
/* ======= Minimal Footer Styles ======= */
.footer {
  background: #f8f9fa;
  border-top: 1px solid #e9ecef;
  padding: 0.75rem 0;
  font-size: 0.85rem;
  color: #495057;
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
  color: #1a237e;
}

.footer-tagline {
  color: #6c757d;
  position: relative;
  padding-left: 0.75rem;
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