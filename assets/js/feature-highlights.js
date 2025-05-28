// Feature Highlights Effects
document.addEventListener('DOMContentLoaded', function() {
  const featureHighlights = document.querySelectorAll('.feature-highlight');
  
  // Skip if no feature highlights found
  if (!featureHighlights.length) return;
  
  // Setup particles for each feature highlight
  featureHighlights.forEach(highlight => {
    // Create particle container
    const particlesContainer = document.createElement('div');
    particlesContainer.className = 'feature-particles';
    
    // Create particles
    for (let i = 0; i < 5; i++) {
      const particle = document.createElement('div');
      particle.className = 'feature-particle';
      particlesContainer.appendChild(particle);
    }
    
    // Add to the highlight
    highlight.appendChild(particlesContainer);
    
    // Add mouse follow effect
    highlight.addEventListener('mousemove', (e) => {
      const rect = highlight.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      // Calculate relative position (0 to 1)
      const relX = x / rect.width;
      const relY = y / rect.height;
      
      // Apply 3D rotation based on cursor position
      // The multiplier controls the amount of tilt
      const tiltAmount = 5;
      const tiltX = (relY - 0.5) * tiltAmount;
      const tiltY = (0.5 - relX) * tiltAmount;
      
      highlight.style.transform = `perspective(1000px) translateZ(10px) rotateX(${tiltX}deg) rotateY(${tiltY}deg)`;
      
      // Move particles relative to cursor
      const particles = highlight.querySelectorAll('.feature-particle');
      particles.forEach((particle, index) => {
        // Custom movement pattern for each particle
        const offsetX = (relX - 0.5) * 50 * (index % 3 + 1);
        const offsetY = (relY - 0.5) * 50 * (index % 2 + 1);
        
        particle.style.transform = `translate(
          calc(-50% + ${offsetX}px), 
          calc(-50% + ${offsetY}px)
        )`;
      });
    });
    
    // Reset transform on mouse leave
    highlight.addEventListener('mouseleave', () => {
      highlight.style.transform = 'perspective(1000px) translateZ(0) rotateX(0) rotateY(0)';
      
      // Reset particles
      const particles = highlight.querySelectorAll('.feature-particle');
      particles.forEach(particle => {
        particle.style.transform = 'translate(-50%, -50%)';
      });
    });
    
    // Add click effect
    highlight.addEventListener('mousedown', () => {
      highlight.style.transform = 'perspective(1000px) translateZ(5px) scale(0.98)';
    });
    
    highlight.addEventListener('mouseup', () => {
      highlight.style.transform = 'perspective(1000px) translateZ(10px)';
    });
  });
  
  // Intersection Observer for entrance animation
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  
  featureHighlights.forEach(highlight => {
    observer.observe(highlight);
  });
});
