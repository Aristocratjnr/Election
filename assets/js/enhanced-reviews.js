/**
 * Enhanced Reviews Section JavaScript
 * This script handles the improved testimonial slider and animations
 */
document.addEventListener('DOMContentLoaded', function() {
  // Initialize enhanced review swiper
  const reviewsSwiper = new Swiper('#swiper-reviews', {
    slidesPerView: 1,
    spaceBetween: 20,
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
      pauseOnMouseEnter: true
    },
    effect: 'slide',
    speed: 800,
    pagination: {
      el: '.reviews-pagination',
      clickable: true,
      dynamicBullets: true,
    },
    navigation: {
      nextEl: '#reviews-next-btn',
      prevEl: '#reviews-previous-btn',
    },
    breakpoints: {
      // when window width is >= 576px
      576: {
        slidesPerView: 1,
        spaceBetween: 20
      },
      // when window width is >= 768px
      768: {
        slidesPerView: 2,
        spaceBetween: 20
      },
      // when window width is >= 992px
      992: {
        slidesPerView: 2,
        spaceBetween: 30
      },
      // when window width is >= 1200px
      1200: {
        slidesPerView: 2.5,
        spaceBetween: 30
      }
    },
    on: {
      init: function() {
        addReviewCardHoverEffects();
      }
    }
  });

  // Add hover effects to review cards
  function addReviewCardHoverEffects() {
    const reviewCards = document.querySelectorAll('.review-card');
    
    reviewCards.forEach(card => {
      // Random subtle rotation for cards (-2 to 2 degrees)
      const randomRotation = (Math.random() * 4 - 2).toFixed(1);
      card.style.transform = `rotate(${randomRotation}deg)`;
      
      card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px) rotate(0deg)';
      });
      
      card.addEventListener('mouseleave', function() {
        this.style.transform = `translateY(0) rotate(${randomRotation}deg)`;
      });
    });
  }

  // Add subtle parallax effect to background elements
  const reviewsBgElements = document.querySelectorAll('.reviews-bg-element');
  window.addEventListener('mousemove', function(e) {
    const mouseX = e.clientX / window.innerWidth;
    const mouseY = e.clientY / window.innerHeight;
    
    reviewsBgElements.forEach(element => {
      const moveX = (mouseX - 0.5) * 20;
      const moveY = (mouseY - 0.5) * 20;
      element.style.transform = `translate(${moveX}px, ${moveY}px)`;
    });
  });

  // Animate review stars on hover
  const reviewStars = document.querySelectorAll('.review-stars');
  reviewStars.forEach(starsContainer => {
    starsContainer.addEventListener('mouseenter', function() {
      const stars = this.querySelectorAll('i');
      stars.forEach((star, index) => {
        setTimeout(() => {
          star.classList.add('star-pulse');
        }, index * 100);
      });
    });
    
    starsContainer.addEventListener('mouseleave', function() {
      const stars = this.querySelectorAll('i');
      stars.forEach(star => {
        star.classList.remove('star-pulse');
      });
    });
  });

  // Add animation for quote icon
  const quoteIcons = document.querySelectorAll('.review-quote-icon');
  quoteIcons.forEach(icon => {
    icon.addEventListener('mouseenter', function() {
      this.style.transform = 'scale(1.2) rotate(10deg)';
      this.style.opacity = '0.8';
    });
    
    icon.addEventListener('mouseleave', function() {
      this.style.transform = 'scale(1) rotate(0deg)';
      this.style.opacity = '0.3';
    });
  });
  
  // Add custom styling to pagination bullets
  const paginationBullets = document.querySelectorAll('.swiper-pagination-bullet');
  paginationBullets.forEach((bullet, index) => {
    bullet.addEventListener('mouseenter', function() {
      if (!this.classList.contains('swiper-pagination-bullet-active')) {
        this.style.width = '15px';
        this.style.background = 'var(--bs-primary)';
        this.style.opacity = '0.6';
      }
    });
    
    bullet.addEventListener('mouseleave', function() {
      if (!this.classList.contains('swiper-pagination-bullet-active')) {
        this.style.width = '10px';
        this.style.background = 'rgba(var(--bs-primary-rgb), 0.3)';
        this.style.opacity = '1';
      }
    });
  });
});

// Add CSS for star animation
document.head.insertAdjacentHTML('beforeend', `
<style>
@keyframes starPulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

.star-pulse {
  animation: starPulse 0.5s ease-in-out;
}

.review-quote-icon {
  transition: all 0.3s ease;
}
</style>
`);
