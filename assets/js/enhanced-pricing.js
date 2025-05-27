/**
 * Enhanced Pricing Section Functionality for Election Management System
 * Provides interactive features for the pricing section including:
 * - Monthly/Annual pricing toggle
 * - Pricing comparison table display
 * - FAQ accordion functionality
 * - Price calculation
 */

document.addEventListener('DOMContentLoaded', function() {
  // Enhanced Pricing Toggle Functionality
  const pricingToggle = document.querySelector('.price-duration-toggler');
  const pricingToggleSwitch = document.querySelector('.pricing-toggle-switch');
  const monthlyPrices = document.querySelectorAll('.price-monthly');
  const yearlyPrices = document.querySelectorAll('.price-yearly');
  const yearlyToggle = document.querySelectorAll('.price-yearly-toggle');
  const monthlyLabel = document.querySelector('.pricing-toggle-label.monthly');
  const annualLabel = document.querySelector('.pricing-toggle-label.annual');
  
  // Initialize pricing cards with enhanced styling
  const pricingSection = document.getElementById('landingPricing');
  if (pricingSection) {
    pricingSection.classList.add('enhanced-pricing');
    
    // Add background elements
    const bgElement1 = document.createElement('div');
    bgElement1.classList.add('pricing-bg-element', 'pricing-bg-element-1');
    pricingSection.appendChild(bgElement1);
    
    const bgElement2 = document.createElement('div');
    bgElement2.classList.add('pricing-bg-element', 'pricing-bg-element-2');
    pricingSection.appendChild(bgElement2);
      // Enhance pricing cards with animations and interaction
    const pricingCards = pricingSection.querySelectorAll('.card');
    pricingCards.forEach((card, index) => {
      card.classList.add('pricing-card');
      
      // Add reveal animation with staggered delay
      card.style.opacity = '0';
      card.style.transform = 'translateY(30px)';
      setTimeout(() => {
        card.style.transition = 'all 0.6s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, 100 + (index * 150)); // Staggered animation
      
      // Mark the middle card as popular if there are 3 cards
      if (pricingCards.length === 3 && index === 1) {
        card.classList.add('popular');
        
        const popularBadge = document.createElement('div');
        popularBadge.classList.add('pricing-card-popular-badge');
        popularBadge.textContent = 'Most Popular';
        card.appendChild(popularBadge);
        
        // Add subtle pulse animation to popular card
        setTimeout(() => {
          popularBadge.style.animation = 'pulseBadge 2s infinite';
        }, 1500);
      }
      
      // Enhance card headers
      const cardHeader = card.querySelector('.card-header');
      if (cardHeader) {
        cardHeader.classList.add('pricing-card-header');
      }
      
      // Enhance card body
      const cardBody = card.querySelector('.card-body');
      if (cardBody) {
        cardBody.classList.add('pricing-card-body');
        
        // Enhance pricing features list
        const featuresList = cardBody.querySelector('.pricing-list');
        if (featuresList) {
          featuresList.classList.add('pricing-features-list');
          
          // Enhance feature items
          const featureItems = featuresList.querySelectorAll('li');
          featureItems.forEach(item => {
            item.classList.add('pricing-feature-item');
            
            const featureHeading = item.querySelector('h6');
            if (featureHeading) {
              featureHeading.classList.add('pricing-feature-text');
              
              const featureIcon = featureHeading.querySelector('.badge');
              if (featureIcon) {
                featureIcon.classList.add('pricing-feature-icon', 'included');
              }
            }
          });
        }
        
        // Enhance CTA button
        const ctaButton = cardBody.querySelector('.btn');
        if (ctaButton) {
          ctaButton.classList.add('pricing-btn', 'pricing-btn-primary');
        }
      }
    });
  }
    // Pricing Toggle Functionality with smooth animations
  if (pricingToggle) {
    // Add attention animation to pricing toggle after page load
    setTimeout(() => {
      if (pricingToggleSwitch) {
        pricingToggleSwitch.classList.add('animate-attention');
        setTimeout(() => {
          pricingToggleSwitch.classList.remove('animate-attention');
        }, 1000);
      }
    }, 2000);
    
    pricingToggle.addEventListener('change', function() {
      const isYearly = this.checked;
      
      if (pricingToggleSwitch) {
        if (isYearly) {
          pricingToggleSwitch.classList.add('toggled');
          if (annualLabel) annualLabel.classList.add('active');
          if (monthlyLabel) monthlyLabel.classList.remove('active');
        } else {
          pricingToggleSwitch.classList.remove('toggled');
          if (monthlyLabel) monthlyLabel.classList.add('active');
          if (annualLabel) annualLabel.classList.remove('active');
        }
      }
      
      // Toggle price display with fade animation
      const fadeOutItems = isYearly ? monthlyPrices : yearlyPrices;
      const fadeInItems = isYearly ? yearlyPrices : monthlyPrices;
      
      // Fade out current prices
      fadeOutItems.forEach(price => {
        price.style.opacity = '0';
        setTimeout(() => {
          price.classList.add('d-none');
          // Fade in new prices
          fadeInItems.forEach(newPrice => {
            newPrice.classList.remove('d-none');
            // Force reflow
            newPrice.offsetHeight;
            setTimeout(() => {
              newPrice.style.opacity = '1';
            }, 10);
          });
        }, 200);
      });
      
      // Toggle yearly text with animation
      yearlyToggle.forEach(toggle => {
        if (isYearly) {
          toggle.classList.remove('d-none');
          setTimeout(() => {
            toggle.style.opacity = '1';
            toggle.style.transform = 'translateY(0)';
          }, 200);
        } else {
          toggle.style.opacity = '0';
          toggle.style.transform = 'translateY(-10px)';
          setTimeout(() => {
            toggle.classList.add('d-none');
          }, 200);
        }
      });
      
      // Update pricing cards to highlight savings
      document.querySelectorAll('.pricing-card').forEach(card => {
        if (isYearly) {
          card.classList.add('yearly-active');
        } else {
          card.classList.remove('yearly-active');
        }
      });
    });
    
    // Initial state setup based on the checkbox
    if (pricingToggle.checked) {
      if (pricingToggleSwitch) pricingToggleSwitch.classList.add('toggled');
      if (annualLabel) annualLabel.classList.add('active');
      if (monthlyLabel) monthlyLabel.classList.remove('active');
      
      monthlyPrices.forEach(price => price.classList.add('d-none'));
      yearlyPrices.forEach(price => price.classList.remove('d-none'));
      yearlyToggle.forEach(toggle => toggle.classList.remove('d-none'));
    } else {
      if (pricingToggleSwitch) pricingToggleSwitch.classList.remove('toggled');
      if (monthlyLabel) monthlyLabel.classList.add('active');
      if (annualLabel) annualLabel.classList.remove('active');
      
      monthlyPrices.forEach(price => price.classList.remove('d-none'));
      yearlyPrices.forEach(price => price.classList.add('d-none'));
      yearlyToggle.forEach(toggle => toggle.classList.add('d-none'));
    }
  }
  
  // Add pricing comparison toggle if it doesn't exist
  if (pricingSection && !document.querySelector('.pricing-comparison-toggle')) {
    const comparisonContainer = document.createElement('div');
    comparisonContainer.classList.add('container', 'pricing-comparison-container');
    
    const comparisonToggle = document.createElement('div');
    comparisonToggle.classList.add('pricing-comparison-toggle');
    
    const comparisonBtn = document.createElement('button');
    comparisonBtn.classList.add('pricing-comparison-btn');
    comparisonBtn.textContent = 'View Full Feature Comparison';
    comparisonBtn.type = 'button';
    
    const comparisonTable = document.createElement('div');
    comparisonTable.classList.add('pricing-comparison-table');
      // Create table HTML with improved visuals
    comparisonTable.innerHTML = `
      <div class="table-responsive">
        <table class="pricing-table">
          <thead>
            <tr>
              <th class="feature-column">Features</th>
              <th class="free-column">Free For Students</th>
              <th class="team-column highlight-column">Team</th>
              <th class="enterprise-column">Enterprise</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="feature-name">Registered voters</td>
              <td>Up to 500</td>
              <td class="highlight-cell">Up to 5,000</td>
              <td><span class="unlimited-badge">Unlimited</span></td>
            </tr>
            <tr>
              <td class="feature-name">Standard security features</td>
              <td><i class="bx bx-check pricing-check-icon"></i></td>
              <td class="highlight-cell"><i class="bx bx-check pricing-check-icon"></i></td>
              <td><i class="bx bx-check pricing-check-icon"></i></td>
            </tr>
            <tr>
              <td class="feature-name">Basic analytics</td>
              <td><i class="bx bx-check pricing-check-icon"></i></td>
              <td class="highlight-cell"><i class="bx bx-check pricing-check-icon"></i></td>
              <td><i class="bx bx-check pricing-check-icon"></i></td>
            </tr>
            <tr>
              <td class="feature-name">Email support</td>
              <td><i class="bx bx-check pricing-check-icon"></i></td>
              <td class="highlight-cell"><i class="bx bx-check pricing-check-icon"></i></td>
              <td><i class="bx bx-check pricing-check-icon"></i></td>
            </tr>
            <tr>
              <td class="feature-name">Advanced ballot design tools</td>
              <td><i class="bx bx-x pricing-times-icon"></i></td>
              <td class="highlight-cell"><i class="bx bx-check pricing-check-icon"></i></td>
              <td><i class="bx bx-check pricing-check-icon premium-feature"></i></td>
            </tr>
            <tr>
              <td class="feature-name">Enhanced security features</td>
              <td><i class="bx bx-x pricing-times-icon"></i></td>
              <td class="highlight-cell"><i class="bx bx-check pricing-check-icon"></i></td>
              <td><i class="bx bx-check pricing-check-icon premium-feature"></i></td>
            </tr>
            <tr>
              <td class="feature-name">Priority email & phone support</td>
              <td><i class="bx bx-x pricing-times-icon"></i></td>
              <td class="highlight-cell"><i class="bx bx-check pricing-check-icon"></i></td>
              <td><i class="bx bx-check pricing-check-icon premium-feature"></i></td>
            </tr>
            <tr>
              <td class="feature-name">Advanced analytics</td>
              <td><i class="bx bx-x pricing-times-icon"></i></td>
              <td class="highlight-cell"><i class="bx bx-check pricing-check-icon"></i></td>
              <td><i class="bx bx-check pricing-check-icon premium-feature"></i></td>
            </tr>
            <tr>
              <td class="feature-name">Custom branding</td>
              <td><i class="bx bx-x pricing-times-icon"></i></td>
              <td class="highlight-cell"><i class="bx bx-check pricing-check-icon"></i></td>
              <td><i class="bx bx-check pricing-check-icon premium-feature"></i></td>
            </tr>
            <tr>
              <td class="feature-name">API Access</td>
              <td><i class="bx bx-x pricing-times-icon"></i></td>
              <td class="highlight-cell"><i class="bx bx-x pricing-times-icon"></i></td>
              <td><i class="bx bx-check pricing-check-icon premium-feature"></i></td>
            </tr>
            <tr>
              <td class="feature-name">Advanced reporting</td>
              <td><i class="bx bx-x pricing-times-icon"></i></td>
              <td class="highlight-cell"><i class="bx bx-x pricing-times-icon"></i></td>
              <td><i class="bx bx-check pricing-check-icon premium-feature"></i></td>
            </tr>
            <tr>
              <td class="feature-name">Dedicated account manager</td>
              <td><i class="bx bx-x pricing-times-icon"></i></td>
              <td class="highlight-cell"><i class="bx bx-x pricing-times-icon"></i></td>
              <td><i class="bx bx-check pricing-check-icon premium-feature"></i></td>
            </tr>
            <tr>
              <td class="feature-name">Custom development</td>
              <td><i class="bx bx-x pricing-times-icon"></i></td>
              <td class="highlight-cell"><i class="bx bx-x pricing-times-icon"></i></td>
              <td><i class="bx bx-check pricing-check-icon premium-feature"></i></td>
            </tr>
          </tbody>
        </table>
      </div>
    `;
    
    comparisonToggle.appendChild(comparisonBtn);
    comparisonContainer.appendChild(comparisonToggle);
    comparisonContainer.appendChild(comparisonTable);
    
    // Add comparison container after pricing cards
    const pricingCardsRow = pricingSection.querySelector('.row.g-6');
    if (pricingCardsRow) {
      pricingCardsRow.after(comparisonContainer);
    } else {
      pricingSection.appendChild(comparisonContainer);
    }
    
    // Toggle comparison table visibility
    comparisonBtn.addEventListener('click', function() {
      comparisonTable.classList.toggle('show');
      this.textContent = comparisonTable.classList.contains('show') 
        ? 'Hide Feature Comparison' 
        : 'View Full Feature Comparison';
    });
  }    // Add Success Stories showcase if it doesn't exist
  if (pricingSection && !document.querySelector('.pricing-success-stories')) {
    const successContainer = document.createElement('div');
    successContainer.classList.add('container', 'pricing-success-stories');
    
    const successTitle = document.createElement('h3');
    successTitle.classList.add('pricing-success-title');
    successTitle.textContent = 'Success Stories & Impact';
    
    const testimonialList = document.createElement('div');
    testimonialList.classList.add('pricing-testimonials-list');
      // Success story items with metrics
    const successStories = [
      {
        title: 'University-Wide Election Success',
        organization: 'State University System',
        metrics: {
          voterTurnout: '94%',
          timeReduction: '75%',
          votesProcessed: '50,000+'
        },
        impact: 'Achieved highest voter turnout in university history with zero reported security incidents',
        image: 'assets/img/success/university-case.svg'
      },
      {        name: 'David Chen',
        role: 'Election Commissioner',
        organization: 'National Bar Association',
        quote: 'The Enterprise plan features helped us manage complex board elections seamlessly. The support team was exceptional in customizing the system to our needs.',
        rating: 5,
        avatar: 'assets/img/avatars/commissioner.jpg'
      },
      {
        name: 'Emily Rodriguez',
        answer: `<p>Yes, you can upgrade your plan at any time as your organization's needs grow.</p>
                <p>When you upgrade:</p>
                <ul>
                  <li>We'll prorate your billing so you only pay the difference</li>
                  <li>Your election data will be seamlessly transferred</li>
                  <li>No disruption to ongoing or scheduled elections</li>
                  <li>Immediate access to all new features</li>
                </ul>
                <p>You can manage your subscription from your account dashboard or contact our support team for assistance.</p>`
      },      {
        name: 'Michael Thompson',
        role: 'IT Director',
        organization: 'City Government',
        quote: 'The blockchain verification and security features give us complete confidence in the integrity of our election results. A game-changer for digital voting.',
        rating: 5,
        avatar: 'assets/img/avatars/it-director.jpg'
      },
      {
        name: 'Lisa Park',
        answer: `<p>Yes, we offer flexible options to try our platform:</p>
                <ul>
                  <li>A comprehensive 14-day free trial for our Team plan</li>
                  <li>Free demo account with sample elections to explore features</li>
                  <li>Guided product tours with our customer success team</li>
                  <li>For Enterprise plan, we provide a tailored demonstration and consultation</li>
                </ul>
                <p>No credit card is required for the trial, and you'll receive email reminders before it ends.</p>`
      },
      {
        question: 'How do you handle data privacy and compliance?',
        answer: `<p>We take data privacy very seriously and comply with major regulations including:</p>
                <ul>
                  <li>GDPR for European users</li>
                  <li>CCPA for California residents</li>
                  <li>HIPAA for healthcare-related elections (Enterprise plan)</li>
                  <li>FERPA for educational institutions</li>
                </ul>
                <p>All user data is encrypted both in transit and at rest. We maintain a comprehensive Data Processing Agreement (DPA) and Privacy Policy that details how we collect, process, and protect your information.</p>
                <p>Enterprise customers can request custom data retention policies and additional compliance certifications.</p>`
      },
      {
        question: 'What voter verification methods do you support?',
        answer: `<p>We offer multiple verification methods to ensure election integrity:</p>
                <ul>
                  <li>Email verification with secure tokens</li>
                  <li>SMS verification codes</li>
                  <li>Single Sign-On (SSO) integration</li>
                  <li>Multi-factor authentication</li>
                  <li>Enterprise: Custom ID verification workflows</li>
                  <li>Enterprise: Biometric verification options</li>
                </ul>
                <p>You can select the verification method that best suits your organization's security requirements and user experience needs.</p>`
      }
    ];    // Create Success Story cards with animation
    successStories.forEach((story, index) => {
      const storyCard = document.createElement('div');
      storyCard.classList.add('pricing-success-card');
      
      const storyContent = document.createElement('div');
      storyContent.classList.add('success-content');
      
      // Create metrics grid
      const metricsGrid = document.createElement('div');
      metricsGrid.classList.add('success-metrics-grid');
      
      Object.entries(story.metrics).forEach(([key, value]) => {
        const metricItem = document.createElement('div');
        metricItem.classList.add('success-metric-item');
        metricItem.innerHTML = `
          <div class="metric-value">${value}</div>
          <div class="metric-label">${key.replace(/([A-Z])/g, ' $1').toLowerCase()}</div>
        `;
        metricsGrid.appendChild(metricItem);
      });const testimonialInfo = document.createElement('div');
      testimonialInfo.classList.add('pricing-testimonial-info');
      testimonialInfo.innerHTML = `
        <div class="pricing-testimonial-avatar">
          <img src="${item.avatar}" alt="${item.name}" onError="this.src='assets/img/avatars/default.jpg'">
        </div>
        <div class="pricing-testimonial-meta">
          <h4>${item.name}</h4>
          <p class="role">${item.role}</p>
          <p class="organization">${item.organization}</p>
          <div class="rating">
            ${'★'.repeat(item.rating)}${'☆'.repeat(5-item.rating)}
          </div>
        </div>
      `;
      
      testimonialContent.appendChild(testimonialQuote);
      testimonialContent.appendChild(testimonialInfo);
      testimonialItem.appendChild(testimonialContent);
      testimonialList.appendChild(testimonialItem);
        // Toggle FAQ accordion with improved animation
      faqQuestion.addEventListener('click', function() {
        const isActive = faqItem.classList.contains('active');
        
        // Close all FAQ items with smooth animation
        document.querySelectorAll('.pricing-faq-item').forEach(item => {
          const itemAnswer = item.querySelector('.pricing-faq-answer');
          if (item.classList.contains('active')) {
            // Set exact height before transitioning to 0
            itemAnswer.style.maxHeight = itemAnswer.scrollHeight + 'px';
            // Force reflow
            itemAnswer.offsetHeight;
            // Now animate to 0
            setTimeout(() => {
              itemAnswer.style.maxHeight = '0px';
              item.classList.remove('active');
            }, 10);
          }
        });
        
        // Open clicked item if it wasn't active
        if (!isActive) {
          setTimeout(() => {
            faqItem.classList.add('active');
            // First set height to 0, then animate to scrollHeight
            faqAnswer.style.maxHeight = '0px';
            // Force reflow
            faqAnswer.offsetHeight;
            // Now animate to full height
            faqAnswer.style.maxHeight = faqAnswer.scrollHeight + 'px';
          }, 200); // Small delay for better visual sequence
        }
      });
    });
      testimonialContainer.appendChild(testimonialTitle);
    testimonialContainer.appendChild(testimonialList);
    
    // Add Testimonials section after comparison container
    const comparisonContainer = document.querySelector('.pricing-comparison-container');
    if (comparisonContainer) {
      comparisonContainer.after(testimonialContainer);
    } else {
      pricingSection.appendChild(testimonialContainer);
    }

    // Add animation to testimonials as they scroll into view
    const observerOptions = {
      threshold: 0.2,
      rootMargin: '0px'
    };

    const testimonialObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-in');
          testimonialObserver.unobserve(entry.target);
        }
      });
    }, observerOptions);

    document.querySelectorAll('.pricing-testimonial-item').forEach(item => {
      testimonialObserver.observe(item);
    });
  }
});
