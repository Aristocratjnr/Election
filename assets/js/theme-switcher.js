/**
 * Enhanced Theme Switcher for SmartVote
 * Provides smooth transitions between light and dark themes
 */
document.addEventListener('DOMContentLoaded', function() {
  // Create theme transition stylesheet
  const styleElement = document.createElement('style');
  styleElement.textContent = `
    .theme-transition,
    .theme-transition *,
    .theme-transition *:before,
    .theme-transition *:after {
      transition: background-color 0.3s ease, 
                  border-color 0.3s ease, 
                  color 0.3s ease, 
                  box-shadow 0.3s ease !important;
    }
    
    .theme-toggle-wrapper {
      position: relative;
      display: inline-flex;
      align-items: center;
      padding: 0.25rem;
      border-radius: 2rem;
      background: var(--bs-body-bg);
      border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
      transition: all 0.3s ease;
    }
    
    .theme-toggle-wrapper:hover {
      border-color: rgba(var(--bs-primary-rgb), 0.4);
    }
    
    .theme-toggle {
      width: 48px;
      height: 24px;
      border-radius: 12px;
      position: relative;
      cursor: pointer;
      background: rgba(var(--bs-primary-rgb), 0.15);
      transition: background 0.3s ease;
    }
    
    .theme-toggle:hover {
      background: rgba(var(--bs-primary-rgb), 0.25);
    }
    
    .theme-toggle-track {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      border-radius: 12px;
      overflow: hidden;
    }
    
    .theme-toggle-thumb {
      position: absolute;
      top: 2px;
      left: 2px;
      width: 20px;
      height: 20px;
      background-color: #fff;
      border-radius: 50%;
      transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1), 
                  background-color 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    [data-bs-theme="dark"] .theme-toggle-thumb {
      transform: translateX(24px);
      background-color: #2b2c40;
    }
    
    .theme-icon {
      font-size: 12px;
      color: var(--bs-primary);
      transition: opacity 0.3s ease;
    }
    
    .theme-toggle-sun,
    .theme-toggle-moon {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      font-size: 12px;
      transition: all 0.3s ease;
    }
    
    .theme-toggle-sun {
      left: 6px;
      color: #ff9500;
      opacity: 1;
    }
    
    .theme-toggle-moon {
      right: 6px;
      color: #6e6aed;
      opacity: 0.5;
    }
    
    [data-bs-theme="dark"] .theme-toggle-sun {
      opacity: 0.5;
    }
    
    [data-bs-theme="dark"] .theme-toggle-moon {
      opacity: 1;
    }
    
    /* Animation for active theme item in dropdown */
    .dropdown-item.active-theme {
      background-color: rgba(var(--bs-primary-rgb), 0.1);
      position: relative;
    }
    
    .dropdown-item.active-theme::after {
      content: "✓";
      position: absolute;
      right: 1rem;
      color: var(--bs-primary);
    }
    
    /* Theme label styles */
    .theme-name {
      margin-left: 0.5rem;
      font-weight: 500;
      font-size: 0.9rem;
    }
    
    @media (max-width: 767.98px) {
      .theme-name {
        display: none;
      }
    }
  `;
  document.head.appendChild(styleElement);

  // Theme management functions
  const getPreferredTheme = () => {
    const storedTheme = localStorage.getItem('theme');
    if (storedTheme) {
      return storedTheme;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };

  const setTheme = (theme) => {
    const root = document.documentElement;
    let actualTheme = theme;
    
    if (theme === 'auto') {
      actualTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    
    // Add transition class for smooth color changes
    root.classList.add('theme-transition');
    root.setAttribute('data-bs-theme', actualTheme);
    
    // Update UI elements that might need special handling
    const header = document.querySelector('nav.navbar');
    if (header) {
      if (actualTheme === 'dark') {
        header.classList.remove('bg-white');
        header.classList.add('bg-dark');
      } else {
        header.classList.remove('bg-dark');
        header.classList.add('bg-white');
      }
    }

    // Store theme preference
    localStorage.setItem('theme', theme);

    // Update active state in dropdown
    document.querySelectorAll('[data-bs-theme-value]').forEach(item => {
      item.classList.remove('active-theme');
      if (item.getAttribute('data-bs-theme-value') === theme) {
        item.classList.add('active-theme');
      }
    });

    // Update toggle state
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
      if (actualTheme === 'dark') {
        themeToggle.setAttribute('aria-checked', 'true');
      } else {
        themeToggle.setAttribute('aria-checked', 'false');
      }
    }

    // Remove transition class after colors have changed
    setTimeout(() => {
      root.classList.remove('theme-transition');
    }, 300);

    // Dispatch theme change event
    document.dispatchEvent(new CustomEvent('themeChanged', { 
      detail: { theme: actualTheme }
    }));
  };

  // Initialize theme
  setTheme(getPreferredTheme());

  // Watch for system theme changes
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (localStorage.getItem('theme') === 'auto') {
      setTheme('auto');
    }
  });

  // Replace the existing theme dropdown with enhanced version
  const replaceThemeDropdown = () => {
    const themeDropdown = document.querySelector('.theme-switch-wrapper');
    if (!themeDropdown) return;
    
    const currentTheme = getPreferredTheme();
    const isSystemTheme = currentTheme === 'auto';
    const isDarkTheme = currentTheme === 'dark' || (isSystemTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);
    
    const newThemeDropdown = document.createElement('li');
    newThemeDropdown.className = 'nav-item dropdown me-3 theme-switch-wrapper';
    newThemeDropdown.innerHTML = `
      <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="themeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <div class="theme-toggle-wrapper me-2">
          <div class="theme-toggle" role="switch" aria-checked="${isDarkTheme ? 'true' : 'false'}" tabindex="0">
            <div class="theme-toggle-track">
              <i class="bx bx-sun theme-toggle-sun"></i>
              <i class="bx bx-moon theme-toggle-moon"></i>
            </div>
            <div class="theme-toggle-thumb"></div>
          </div>
        </div>
        <span class="theme-name">Theme</span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end enhanced-dropdown" aria-labelledby="themeDropdown">
        <li>
          <button class="dropdown-item d-flex align-items-center theme-item ${currentTheme === 'light' ? 'active-theme' : ''}" type="button" data-bs-theme-value="light">
            <i class="bx bx-sun me-2"></i>
            <span>Light</span>
          </button>
        </li>
        <li>
          <button class="dropdown-item d-flex align-items-center theme-item ${currentTheme === 'dark' ? 'active-theme' : ''}" type="button" data-bs-theme-value="dark">
            <i class="bx bx-moon me-2"></i>
            <span>Dark</span>
          </button>
        </li>
        <li>
          <button class="dropdown-item d-flex align-items-center theme-item ${currentTheme === 'auto' ? 'active-theme' : ''}" type="button" data-bs-theme-value="auto">
            <i class="bx bx-desktop me-2"></i>
            <span>System</span>
          </button>
        </li>
      </ul>
    `;
    
    themeDropdown.parentNode.replaceChild(newThemeDropdown, themeDropdown);
    
    // Add toggle functionality
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
      themeToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        const currentTheme = getPreferredTheme();
        const actualTheme = currentTheme === 'auto' 
          ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
          : currentTheme;
          
        if (actualTheme === 'dark') {
          setTheme('light');
        } else {
          setTheme('dark');
        }
      });
      
      themeToggle.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          themeToggle.click();
        }
      });
    }
    
    // Add dropdown item functionality
    document.querySelectorAll('[data-bs-theme-value]').forEach(item => {
      item.addEventListener('click', () => {
        const theme = item.getAttribute('data-bs-theme-value');
        setTheme(theme);
      });
    });
  };
  
  // Replace theme dropdown after a short delay to ensure DOM is ready
  setTimeout(replaceThemeDropdown, 100);
});