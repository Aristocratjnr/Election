// Add entrance animation for review cards
document.addEventListener('DOMContentLoaded', function() {
  // Set animation delay for each slide
  const slides = document.querySelectorAll('.swiper-slide');
  slides.forEach((slide, index) => {
    slide.style.setProperty('--slide-index', index);
  });
  
  // Add star animation delay for each star
  const reviewCards = document.querySelectorAll('.review-card');
  reviewCards.forEach(card => {
    const stars = card.querySelectorAll('.review-stars i');
    stars.forEach((star, index) => {
      star.style.setProperty('--i', index);
    });
    
    // Add hover effect enhancements
    card.addEventListener('mouseenter', function() {
      this.classList.add('card-active');
      
      // Add subtle rotation to quote icon
      const quoteIcon = this.querySelector('.review-quote-icon');
      if (quoteIcon) {
        quoteIcon.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
      }
    });
    
    card.addEventListener('mouseleave', function() {
      this.classList.remove('card-active');
    });
  });
  
  // Enhanced pagination bullets
  const paginationBullets = document.querySelectorAll('.swiper-pagination-bullet');
  paginationBullets.forEach(bullet => {
    bullet.addEventListener('mouseenter', function() {
      this.style.transform = 'scale(1.2)';
    });
    
    bullet.addEventListener('mouseleave', function() {
      if (!this.classList.contains('swiper-pagination-bullet-active')) {
        this.style.transform = 'scale(1)';
      }
    });
  });
  
  // Intersection Observer for animations when scrolling into view
  const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
  };
  
  const reviewObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.animationPlayState = 'running';
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);
  
  // Observe each review card for scroll animations
  document.querySelectorAll('.review-card').forEach(card => {
    card.style.animationPlayState = 'paused';
    reviewObserver.observe(card);
  });
});
