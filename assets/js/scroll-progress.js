document.addEventListener('DOMContentLoaded', function() {
    // Create the scroll progress container and bar
    const container = document.createElement('div');
    container.className = 'scroll-progress-container';
    const bar = document.createElement('div');
    bar.className = 'scroll-progress-bar';
    container.appendChild(bar);
    document.body.appendChild(container);

    // Create scroll to top button
    const scrollTopBtn = document.createElement('button');
    scrollTopBtn.className = 'scroll-to-top';
    scrollTopBtn.innerHTML = '<i class="bx bx-up-arrow-alt"></i>';
    document.body.appendChild(scrollTopBtn);

    // Update scroll progress and scroll to top button visibility
    function updateScrollProgress() {
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight - windowHeight;
        const scrolled = window.scrollY;
        const progress = (scrolled / documentHeight) * 100;
        
        // Update progress bar
        bar.style.width = progress + '%';
        bar.classList.toggle('active', scrolled > 100);
        
        // Update scroll to top button visibility
        scrollTopBtn.classList.toggle('visible', scrolled > 300);
    }

    // Scroll to top when button is clicked
    scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Add scroll event listener
    window.addEventListener('scroll', updateScrollProgress);
    // Initial update
    updateScrollProgress();
});
