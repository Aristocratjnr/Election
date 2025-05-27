/**
 * Enhanced Navbar Functionality for Election Management System
 * Provides interactive features for the navigation bar including:
 * - Sticky navbar on scroll
 * - Smooth scrolling for navigation links
 * - Mobile menu improvements
 * - Theme switching persistence
 */

document.addEventListener('DOMContentLoaded', function() {
  // Elements
  const navbar = document.querySelector('.navbar');
  const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
  const themeItems = document.querySelectorAll('.theme-item');
  const navbarToggler = document.querySelector('.navbar-toggler');
  const navbarCollapse = document.querySelector('.navbar-collapse');
  
  // Add enhanced navbar class
  navbar.classList.add('enhanced-navbar');
  
  // Make dropdown menus enhanced
  document.querySelectorAll('.dropdown-menu').forEach(menu => {
    menu.classList.add('enhanced-dropdown');
  });
  
  // Add auth button class
  document.querySelectorAll('.nav-item .btn').forEach(btn => {
    btn.classList.add('auth-btn');
  });
  
  // Sticky Navbar on Scroll
  const stickyNavbar = () => {
    if (window.scrollY > 100) {
      navbar.classList.add('navbar-sticky', 'navbar-scrolled');
    } else {
      navbar.classList.remove('navbar-sticky', 'navbar-scrolled');
    }
  };
  
  // Smooth scroll for navigation links
  navLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      const targetId = this.getAttribute('href');
      if (!targetId || targetId === '#') return;
      
      const targetSection = document.querySelector(targetId);
      if (!targetSection) return;
      
      // Close mobile menu if open
      if (navbarCollapse.classList.contains('show')) {
        navbarToggler.click();
      }
      
      // Smooth scroll to target
      window.scrollTo({
        top: targetSection.offsetTop - navbar.offsetHeight - 20,
        behavior: 'smooth'
      });
    });
  });
  
  // Active link highlighting on scroll
  const highlightActiveLink = () => {
    const sections = document.querySelectorAll('section[id]');
    
    sections.forEach(section => {
      const sectionTop = section.offsetTop - navbar.offsetHeight - 50;
      const sectionHeight = section.offsetHeight;
      const sectionId = section.getAttribute('id');
      
      if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
        document.querySelector(`.nav-link[href="#${sectionId}"]`)?.classList.add('active');
      } else {
        document.querySelector(`.nav-link[href="#${sectionId}"]`)?.classList.remove('active');
      }
    });
  };
  
  // Theme switching with localStorage persistence
  themeItems.forEach(item => {
    item.addEventListener('click', function() {
      const theme = this.getAttribute('data-bs-theme-value');
      localStorage.setItem('theme', theme);
      
      // Update active state
      themeItems.forEach(i => i.classList.remove('active'));
      this.classList.add('active');
    });
    
    // Set active state based on current theme
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    const savedTheme = localStorage.getItem('theme');
    const themeValue = item.getAttribute('data-bs-theme-value');
    
    if ((savedTheme && themeValue === savedTheme) || 
        (!savedTheme && themeValue === currentTheme) ||
        (!savedTheme && !currentTheme && themeValue === 'light')) {
      item.classList.add('active');
    }
  });
  
  // Improved mobile menu behavior
  navbarToggler.addEventListener('click', function() {
    // Add animation class when toggling
    if (!navbarCollapse.classList.contains('show')) {
      navbarCollapse.style.maxHeight = '0';
      setTimeout(() => {
        navbarCollapse.style.maxHeight = `${navbarCollapse.scrollHeight}px`;
      }, 10);
    } else {
      navbarCollapse.style.maxHeight = '0';
    }
  });
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    const dropdowns = document.querySelectorAll('.dropdown-menu.show');
    if (dropdowns.length > 0) {
      const isDropdownOrToggle = e.target.closest('.dropdown-menu') || e.target.closest('.dropdown-toggle');
      if (!isDropdownOrToggle) {
        dropdowns.forEach(dropdown => {
          const toggle = dropdown.previousElementSibling;
          if (toggle && toggle.classList.contains('dropdown-toggle')) {
            toggle.click();
          }
        });
      }
    }
  });
  
  // Event listeners
  window.addEventListener('scroll', stickyNavbar);
  window.addEventListener('scroll', highlightActiveLink);
  
  // Initial call to set state on page load
  stickyNavbar();
  highlightActiveLink();
  
  // Handle page refresh to maintain theme
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme) {
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
  }
});
