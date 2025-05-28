/**
 * Enhanced Footer Interactions
 * Adds smooth animations and interactive elements to the footer
 */
document.addEventListener('DOMContentLoaded', function() {
  // Initialize footer animations
  initFooterAnimations();
  
  // Add smooth scroll for footer links
  initSmoothScroll();
  
  // Newsletter form validation and submission feedback
  initNewsletterForm();
});

/**
 * Initialize animations for footer elements with staggered timing
 */
function initFooterAnimations() {
  // Only animate elements once they're in the viewport
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        // Add the visible class to start animations
        entry.target.classList.add('footer-visible');
        // Unobserve the element once it's animated
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.1 // Trigger when 10% of the element is visible
  });
  
  // Observe all animated elements in the footer
  const footerElements = document.querySelectorAll('.footer-animated .footer-logo-container, .footer-animated .footer-tagline, .footer-animated .footer-links-title, .footer-animated .footer-links-list, .footer-animated .footer-form, .footer-animated .footer-social, .footer-animated .footer-bottom');
  
  footerElements.forEach(el => {
    observer.observe(el);
  });
  
  // Add hover animation to social icons
  const socialIcons = document.querySelectorAll('.social-icon-fancy');
  socialIcons.forEach(icon => {
    icon.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-5px)';
    });
    
    icon.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0)';
    });
  });
}

/**
 * Add smooth scrolling to page links in the footer
 */
function initSmoothScroll() {
  const footerLinks = document.querySelectorAll('.footer-links-list a');
  
  footerLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      // Only apply to hash links that point to an element on the page
      const href = this.getAttribute('href');
      if (href.startsWith('#') && href.length > 1) {
        e.preventDefault();
        
        const targetId = href.substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop - 100, // Offset for fixed headers
            behavior: 'smooth'
          });
        }
      }
    });
  });
}

/**
 * Newsletter form validation and submission feedback
 */
function initNewsletterForm() {
  const newsletterForm = document.querySelector('.footer-form');
  
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const emailInput = this.querySelector('input[type="email"]');
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.innerHTML;
      
      // Simple validation
      if (!emailInput.value || !validateEmail(emailInput.value)) {
        // Add shake animation to input
        emailInput.classList.add('invalid-shake');
        setTimeout(() => {
          emailInput.classList.remove('invalid-shake');
        }, 600);
        return;
      }
      
      // Show loading state
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Subscribing...';
      submitBtn.disabled = true;
      
      // Simulate form submission (replace with actual API call)
      setTimeout(() => {
        // Success state
        submitBtn.innerHTML = '<i class="bx bx-check"></i> Subscribed!';
        submitBtn.classList.remove('btn-primary');
        submitBtn.classList.add('btn-success');
        emailInput.value = '';
        
        // Reset after 3 seconds
        setTimeout(() => {
          submitBtn.innerHTML = originalBtnText;
          submitBtn.classList.remove('btn-success');
          submitBtn.classList.add('btn-primary');
          submitBtn.disabled = false;
        }, 3000);
      }, 1500);
    });
  }
}

/**
 * Validate email format
 */
function validateEmail(email) {
  const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(String(email).toLowerCase());
}

// Add CSS for dynamic effects that are created by JS
document.head.insertAdjacentHTML('beforeend', `
<style>
  /* Animation for invalid inputs */
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-10px); }
    40%, 80% { transform: translateX(10px); }
  }
  
  .invalid-shake {
    animation: shake 0.6s cubic-bezier(.36,.07,.19,.97) both;
    border-color: var(--bs-danger) !important;
  }
  
  /* Visibility animations for staggered entrance */
  .footer-animated .footer-logo-container,
  .footer-animated .footer-tagline,
  .footer-animated .footer-links-title,
  .footer-animated .footer-links-list,
  .footer-animated .footer-form,
  .footer-animated .footer-social,
  .footer-animated .footer-bottom {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
  }
  
  .footer-animated .footer-visible {
    opacity: 1;
    transform: translateY(0);
  }
</style>
`);
