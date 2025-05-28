/**
 * Main - Front Pages
 */
'use strict';

window.isRtl = window.Helpers.isRtl();
window.isDarkStyle = window.Helpers.isDarkStyle();

(function () {
  const menu = document.getElementById('navbarSupportedContent'),
    nav = document.querySelector('.layout-navbar') || document.querySelector('.navbar'), // Fix: Add fallback selector
    navItemLink = document.querySelectorAll('.navbar-nav .nav-link');

  // Initialised custom options if checked
  setTimeout(function () {
    window.Helpers.initCustomOptionCheck();
  }, 1000);

  if (typeof Waves !== 'undefined') {
    Waves.init();
    Waves.attach(".btn[class*='btn-']:not([class*='btn-outline-']):not([class*='btn-label-'])", ['waves-light']);
    Waves.attach("[class*='btn-outline-']");
    Waves.attach("[class*='btn-label-']");
    Waves.attach('.pagination .page-item .page-link');
  }

  // Init BS Tooltip
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  if (isRtl) {
    // If layout is RTL add .dropdown-menu-end class to .dropdown-menu
    Helpers._addClass('dropdown-menu-end', document.querySelectorAll('#layout-navbar .dropdown-menu'));
    // If layout is RTL add .dropdown-menu-end class to .dropdown-menu
    Helpers._addClass('dropdown-menu-end', document.querySelectorAll('.dropdown-menu'));
  }

  // Navbar
  if (nav) { // Fix: Check if nav exists before adding event listeners
    window.addEventListener('scroll', e => {
      if (window.scrollY > 10) {
        nav.classList.add('navbar-active');
      } else {
        nav.classList.remove('navbar-active');
      }
    });
    window.addEventListener('load', e => {
      if (window.scrollY > 10) {
        nav.classList.add('navbar-active');
      } else {
        nav.classList.remove('navbar-active');
      }
    });
  }

  // Function to close the mobile menu
  function closeMenu() {
    if (menu) { // Fix: Check if menu exists
      menu.classList.remove('show');
    }
  }

  document.addEventListener('click', function (event) {
    // Check if menu exists and if the clicked element is inside mobile menu
    if (menu && !menu.contains(event.target)) {
      closeMenu();
    }
  });
  
  if (navItemLink.length > 0) { // Fix: Check if navItemLink exists
    navItemLink.forEach(link => {
      link.addEventListener('click', event => {
        if (!link.classList.contains('dropdown-toggle')) {
          closeMenu();
        } else {
          event.preventDefault();
        }
      });
    });
  }

  // Mega dropdown
  const megaDropdown = document.querySelectorAll('.nav-link.mega-dropdown');
  if (megaDropdown.length > 0) { // Fix: Check if megaDropdown exists
    megaDropdown.forEach(e => {
      if (typeof MegaDropdown !== 'undefined') { // Fix: Check if MegaDropdown is defined
        new MegaDropdown(e);
      }
    });
  }

  // Style switcher
  let styleSwitcher = document.querySelector('.dropdown-style-switcher');
  if (styleSwitcher) { // Fix: Check if styleSwitcher exists
    const styleSwitcherIcon = styleSwitcher.querySelector('i');
    
    // Get style from local storage or use 'system' as default
    let storedStyle =
      localStorage.getItem('templateCustomizer-' + (typeof templateName !== 'undefined' ? templateName : 'template') + '--Theme') || //if no template style then use Customizer style
      ((window.templateCustomizer?.settings?.defaultStyle) ?? document.documentElement.getAttribute('data-bs-theme')); //!if there is no Customizer then use default style as light

    if (styleSwitcherIcon) { // Fix: Check if styleSwitcherIcon exists
      new bootstrap.Tooltip(styleSwitcherIcon, {
        title: storedStyle.charAt(0).toUpperCase() + storedStyle.slice(1) + ' Mode',
        fallbackPlacements: ['bottom']
      });
    }

    // Run switchImage function based on the stored style
    if (typeof window.Helpers.switchImage === 'function') { // Fix: Check if switchImage function exists
      window.Helpers.switchImage(storedStyle);
    }
  }

  // Update light/dark image based on current style
  window.Helpers.setTheme(window.Helpers.getPreferredTheme());

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    const storedTheme = window.Helpers.getStoredTheme();
    if (storedTheme !== 'light' && storedTheme !== 'dark') {
      window.Helpers.setTheme(window.Helpers.getPreferredTheme());
    }
  });

  function getScrollbarWidth() {
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    document.body.style.setProperty('--bs-scrollbar-width', `${scrollbarWidth}px`);
  }
  getScrollbarWidth();

  //Style Switcher (Light/Dark/System Mode)
  window.addEventListener('DOMContentLoaded', () => {
    window.Helpers.showActiveTheme(window.Helpers.getPreferredTheme());
    getScrollbarWidth();
    document.querySelectorAll('[data-bs-theme-value]').forEach(toggle => {
      toggle.addEventListener('click', () => {
        const theme = toggle.getAttribute('data-bs-theme-value');
        window.Helpers.setStoredTheme(typeof templateName !== 'undefined' ? templateName : 'template', theme);
        window.Helpers.setTheme(theme);
        window.Helpers.showActiveTheme(theme, true);
        
        if (typeof window.Helpers.syncCustomOptions === 'function') { // Fix: Check if syncCustomOptions function exists
          window.Helpers.syncCustomOptions(theme);
        }
        
        let currTheme = theme;
        if (theme === 'system') {
          currTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        
        if (styleSwitcher) { // Fix: Check if styleSwitcher exists
          const styleSwitcherIcon = styleSwitcher.querySelector('i');
          if (styleSwitcherIcon) { // Fix: Check if styleSwitcherIcon exists
            new bootstrap.Tooltip(styleSwitcherIcon, {
              title: theme.charAt(0).toUpperCase() + theme.slice(1) + ' Mode',
              fallbackPlacements: ['bottom']
            });
          }
        }
        
        if (typeof window.Helpers.switchImage === 'function') { // Fix: Check if switchImage function exists
          window.Helpers.switchImage(currTheme);
        }
      });
    });
  });
})();

// Theme helper functions
window.Helpers = window.Helpers || {};

window.Helpers.getStoredTheme = () => localStorage.getItem('theme');

window.Helpers.getPreferredTheme = () => {
  const storedTheme = window.Helpers.getStoredTheme();
  if (storedTheme) {
    return storedTheme;
  }
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

window.Helpers.setTheme = theme => {
  if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.setAttribute('data-bs-theme', 'dark');
  } else {
    document.documentElement.setAttribute('data-bs-theme', theme);
  }
  
  // Add transition class
  document.documentElement.classList.add('theme-transition');
  
  // Remove transition class after animation
  setTimeout(() => {
    document.documentElement.classList.remove('theme-transition');
  }, 300);
  
  // Dispatch theme change event
  document.dispatchEvent(new CustomEvent('themeChanged', { 
    detail: { theme: theme }
  }));
};

// Theme initialization
document.addEventListener('DOMContentLoaded', () => {
  const theme = window.Helpers.getPreferredTheme();
  window.Helpers.setTheme(theme);
  
  // Watch for system theme changes
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    const storedTheme = window.Helpers.getStoredTheme();
    if (storedTheme !== 'light' && storedTheme !== 'dark') {
      window.Helpers.setTheme(window.Helpers.getPreferredTheme());
    }
  });
});
