// Badge icon particle effects
document.addEventListener('DOMContentLoaded', function() {
  const badgeIcons = document.querySelectorAll('.badge-icon');
  
  badgeIcons.forEach(icon => {
    // Create particle container for each badge
    const particleContainer = document.createElement('div');
    particleContainer.className = 'badge-particles';
    particleContainer.style.position = 'absolute';
    particleContainer.style.width = '100%';
    particleContainer.style.height = '100%';
    particleContainer.style.top = '0';
    particleContainer.style.left = '0';
    particleContainer.style.pointerEvents = 'none';
    particleContainer.style.zIndex = '0';
    particleContainer.style.opacity = '0';
    particleContainer.style.transition = 'opacity 0.5s ease';
    
    // Create particles
    for (let i = 0; i < 5; i++) {
      const particle = document.createElement('div');
      particle.className = 'badge-particle';
      particle.style.position = 'absolute';
      particle.style.width = '5px';
      particle.style.height = '5px';
      particle.style.borderRadius = '50%';
      particle.style.background = 'currentColor';
      particle.style.opacity = '0';
      particle.style.top = '50%';
      particle.style.left = '50%';
      
      // Store animation properties
      particle.dataset.direction = Math.random() * 360;
      particle.dataset.distance = 20 + Math.random() * 30;
      particle.dataset.size = 3 + Math.random() * 4;
      particle.dataset.duration = 1 + Math.random();
      particle.dataset.delay = Math.random() * 0.5;
      
      particleContainer.appendChild(particle);
    }
    
    // Insert particle container
    icon.style.position = 'relative';
    icon.style.zIndex = '1';
    icon.appendChild(particleContainer);
    
    // Add mouse events
    icon.addEventListener('mouseenter', function() {
      particleContainer.style.opacity = '1';
      
      // Animate particles
      const particles = this.querySelectorAll('.badge-particle');
      particles.forEach(particle => {
        // Set particle color based on badge type
        if (icon.closest('.bg-label-primary')) {
          particle.style.backgroundColor = '#696cff';
        } else if (icon.closest('.bg-label-success')) {
          particle.style.backgroundColor = '#71dd37';
        } else if (icon.closest('.bg-label-info')) {
          particle.style.backgroundColor = '#03c3ec';
        } else if (icon.closest('.bg-label-warning')) {
          particle.style.backgroundColor = '#ffab00';
        }
        
        // Get animation properties
        const direction = parseFloat(particle.dataset.direction);
        const distance = parseFloat(particle.dataset.distance);
        const size = parseFloat(particle.dataset.size);
        const duration = parseFloat(particle.dataset.duration);
        const delay = parseFloat(particle.dataset.delay);
        
        // Set particle size
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        
        // Calculate end position
        const radians = direction * (Math.PI / 180);
        const endX = Math.cos(radians) * distance;
        const endY = Math.sin(radians) * distance;
        
        // Reset styles
        particle.style.transition = 'none';
        particle.style.opacity = '0';
        particle.style.transform = 'translate(-50%, -50%)';
        
        // Force reflow
        void particle.offsetWidth;
        
        // Start animation
        particle.style.transition = `all ${duration}s ease-out ${delay}s`;
        particle.style.opacity = '0.8';
        particle.style.transform = `translate(calc(-50% + ${endX}px), calc(-50% + ${endY}px))`;
        
        // Fade out at the end
        setTimeout(() => {
          particle.style.opacity = '0';
        }, (delay + duration * 0.7) * 1000);
      });
    });
    
    icon.addEventListener('mouseleave', function() {
      particleContainer.style.opacity = '0';
      
      // Reset particles
      const particles = this.querySelectorAll('.badge-particle');
      particles.forEach(particle => {
        particle.style.transition = 'none';
        particle.style.opacity = '0';
        particle.style.transform = 'translate(-50%, -50%)';
      });
    });
  });
});
