/**
 * Notiflix (js)
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  const section = document.getElementById('section-block'),
    sectionBtn = document.querySelector('.btn-section-block'),
    sectionBtnOverlay = document.querySelector('.btn-section-block-overlay'),
    sectionBtnSpinner = document.querySelector('.btn-section-block-spinner'),
    sectionBtnCustom = document.querySelector('.btn-section-block-custom'),
    sectionBtnMultiple = document.querySelector('.btn-section-block-multiple'),
    sectionId = '#section-block',
    cardSection = document.querySelector('#card-block'),
    cardBtn = document.querySelector('.btn-card-block'),
    cardBtnOverlay = document.querySelector('.btn-card-block-overlay'),
    cardBtnSpinner = document.querySelector('.btn-card-block-spinner'),
    cardBtnCustom = document.querySelector('.btn-card-block-custom'),
    cardBtnMultiple = document.querySelector('.btn-card-block-multiple'),
    cardSectionId = '#card-block',
    formSection = document.querySelector('.form-block'),
    formBtn = document.querySelector('.btn-form-block'),
    formBtnOverlay = document.querySelector('.btn-form-block-overlay'),
    formBtnSpinner = document.querySelector('.btn-form-block-spinner'),
    formBtnCustom = document.querySelector('.btn-form-block-custom'),
    formBtnMultiple = document.querySelector('.btn-form-block-multiple'),
    formSectionClass = '.form-block',
    optionSection = document.querySelector('#option-block'),
    optionBtn = document.querySelector('.btn-option-block'),
    optionBtnHourglass = document.querySelector('.btn-option-block-hourglass'),
    optionBtnCircle = document.querySelector('.btn-option-block-circle'),
    optionBtnArrows = document.querySelector('.btn-option-block-arrows'),
    optionBtnDots = document.querySelector('.btn-option-block-dots'),
    optionBtnPulse = document.querySelector('.btn-option-block-pulse'),
    optionSectionId = '#option-block',
    pageBtn = document.querySelector('.btn-page-block'),
    pageBtnOverlay = document.querySelector('.btn-page-block-overlay'),
    pageBtnSpinner = document.querySelector('.btn-page-block-spinner'),
    pageBtnCustom = document.querySelector('.btn-page-block-custom'),
    pageBtnMultiple = document.querySelector('.btn-page-block-multiple'),
    removeBtn = document.querySelector('.remove-btn'),
    removeCardBtn = document.querySelector('.remove-card-btn'),
    removeFormBtn = document.querySelector('.remove-form-btn'),
    removeOptionBtn = document.querySelector('.remove-option-btn'),
    removePageBtn = document.querySelector('.remove-page-btn');

  // Notiflix
  // --------------------------------------------------------------------

  // Default
  if (section && sectionBtn) {
    sectionBtn.addEventListener('click', () => {
      Block.circle(sectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '40px',
        svgColor: config.colors.white
      });
    });
  }

  // Overlay Color
  if (section && sectionBtnOverlay) {
    sectionBtnOverlay.addEventListener('click', () => {
      Block.standard(sectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('white-rgb') + ', 0.8)',
        svgSize: '0px'
      });

      let customSpinner = document.createElement('div');
      customSpinner.classList.add('spinner-border', 'text-primary');
      customSpinner.setAttribute('role', 'status');

      let notiflixBlock = document.querySelector('#section-block .notiflix-block');
      notiflixBlock.appendChild(customSpinner);
    });
  }

  // Custom Spinner
  if (section && sectionBtnSpinner) {
    sectionBtnSpinner.addEventListener('click', () => {
      Block.standard(sectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '0px'
      });
      let customSpinnerHTML = `
          <div class="sk-wave mx-auto">
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
          </div>
        `;
      let notiflixBlock = document.querySelector('#section-block .notiflix-block');
      notiflixBlock.innerHTML = customSpinnerHTML;
    });
  }

  // Custom Message
  if (section && sectionBtnCustom) {
    sectionBtnCustom.addEventListener('click', () => {
      Block.standard(sectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '0px'
      });
      let customSpinnerHTML = `
          <div class="d-flex">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
          </div>
        `;
      let notiflixBlock = document.querySelector('#section-block .notiflix-block');
      notiflixBlock.innerHTML = customSpinnerHTML;
    });
  }

  // Multiple Message
  let multiMsg1, multiMsg2, multiMsg3;
  if (section && sectionBtnMultiple) {
    sectionBtnMultiple.addEventListener('click', () => {
      // Step 1: Initial block with spinner and "Please wait..." message
      Block.standard(sectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '0px'
      });

      // Inject custom spinner and message
      let initialMessage = `
            <div class="d-flex justify-content-center">
                <p class="mb-0 text-white">Please wait...</p>
                <div class="sk-wave m-0">
                    <div class="sk-rect sk-wave-rect"></div>
                    <div class="sk-rect sk-wave-rect"></div>
                    <div class="sk-rect sk-wave-rect"></div>
                    <div class="sk-rect sk-wave-rect"></div>
                    <div class="sk-rect sk-wave-rect"></div>
                </div>
            </div>
        `;
      let notiflixBlock = document.querySelector('#section-block .notiflix-block');
      if (notiflixBlock) notiflixBlock.innerHTML = initialMessage;

      // remove the first block
      Block.remove(sectionId, 1000);
      // Timeout to start the second block
      multiMsg1 = setTimeout(() => {
        // Step 2: Second block with "Almost Done..." message
        Block.standard(sectionId, 'Almost Done...', {
          backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
          messageFontSize: '15px',
          svgSize: '0px',
          messageColor: config.colors.white
        });

        // remove the second block
        Block.remove(sectionId, 1000);
        // Timeout to start the third block
        multiMsg2 = setTimeout(() => {
          // Step 3: Final block with "Success" message
          Block.standard(sectionId, {
            backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)'
          });

          let initialMessage = `<div class="px-12 py-3 bg-success text-white">Success</div>`;
          let notiflixBlock = document.querySelector('#section-block .notiflix-block');
          if (notiflixBlock) notiflixBlock.innerHTML = initialMessage;

          multiMsg3 = setTimeout(() => {
            Block.remove(sectionId); // Remove the final block
            setTimeout(() => {
              sectionBtn.classList.remove('disabled');
              sectionBtnOverlay.classList.remove('disabled');
              sectionBtnSpinner.classList.remove('disabled');
              sectionBtnCustom.classList.remove('disabled');
              sectionBtnMultiple.classList.remove('disabled');
            }, 500);
          }, 1810); // Adjust the timeout for the final block
        }, 1810); // Adjust the timeout for the second block
      }, 1610); // Adjust the timeout for the first block
    });
  }

  // List of all button selectors
  const buttonSelectors = [
    '.btn-section-block',
    '.btn-section-block-overlay',
    '.btn-section-block-spinner',
    '.btn-section-block-custom',
    '.btn-section-block-multiple'
  ];

  // Select all buttons based on their individual classes
  const buttons = buttonSelectors.map(selector => document.querySelector(selector));

  // Add click event listener to each button
  buttons.forEach(button => {
    if (button) {
      button.addEventListener('click', () => {
        buttons.forEach(btn => {
          if (btn) {
            btn.classList.add('disabled');
          }
        });
      });
    }
  });

  if (removeBtn) {
    removeBtn.addEventListener('click', () => {
      setTimeout(() => {
        if (document.querySelector(`${sectionId} .notiflix-block`)) {
          Block.remove(sectionId);
        } else {
          alert('No active block to remove.');
        }
      }, 400);
      clearTimeout(multiMsg1);
      clearTimeout(multiMsg2);
      clearTimeout(multiMsg3);
      setTimeout(() => {
        sectionBtn.classList.remove('disabled');
        sectionBtnOverlay.classList.remove('disabled');
        sectionBtnSpinner.classList.remove('disabled');
        sectionBtnCustom.classList.remove('disabled');
        sectionBtnMultiple.classList.remove('disabled');
      }, 500);
    });
  }

  // Card Blocking
  // --------------------------------------------------------------------

  // Default
  if (cardSection && cardBtn) {
    cardBtn.addEventListener('click', () => {
      Block.circle(cardSectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '40px',
        svgColor: config.colors.white
      });
    });
  }

  // Overlay Color
  if (cardSection && cardBtnOverlay) {
    cardBtnOverlay.addEventListener('click', () => {
      Block.standard(cardSectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('white-rgb') + ', 0.8)',
        svgSize: '0px'
      });

      const customSpinner = document.createElement('div');
      customSpinner.classList.add('spinner-border', 'text-primary');
      customSpinner.setAttribute('role', 'status');

      let notiflixBlock = document.querySelector('#card-block .notiflix-block');
      notiflixBlock.appendChild(customSpinner);
    });
  }

  // Custom Spinner
  if (cardSection && cardBtnSpinner) {
    cardBtnSpinner.addEventListener('click', () => {
      Block.standard(cardSectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '0px'
      });
      let customSpinnerHTML = `
          <div class="sk-wave mx-auto">
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
          </div>
        `;
      let notiflixBlock = document.querySelector('#card-block .notiflix-block');
      notiflixBlock.innerHTML = customSpinnerHTML;
    });
  }

  // Custom Message
  if (cardSection && cardBtnCustom) {
    cardBtnCustom.addEventListener('click', () => {
      Block.standard(cardSectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '0px'
      });
      let customSpinnerHTML = `
          <div class="d-flex">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
          </div>
        `;
      let notiflixBlock = document.querySelector('#card-block .notiflix-block');
      notiflixBlock.innerHTML = customSpinnerHTML;
    });
  }

  // Multiple Message
  let multiMsgCard1, multiMsgCard2, multiMsgCard3;
  if (cardSection && cardBtnMultiple) {
    cardBtnMultiple.addEventListener('click', () => {
      // Step 1: Initial block with spinner and "Please wait..." message
      Block.standard(cardSectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '0px'
      });

      // Inject custom spinner and message
      let initialMessageCard = `
            <div class="d-flex justify-content-center">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
            </div>
          `;
      let notiflixBlock = document.querySelector('#card-block .notiflix-block');
      if (notiflixBlock) notiflixBlock.innerHTML = initialMessageCard;

      // remove the first block
      Block.remove(cardSectionId, 1000);
      // Timeout to start the second block
      multiMsgCard1 = setTimeout(() => {
        // Step 2: Second block with "Almost Done..." message
        Block.standard(cardSectionId, 'Almost Done...', {
          backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
          messageFontSize: '15px',
          svgSize: '0px',
          messageColor: config.colors.white
        });

        // remove the second block
        Block.remove(cardSectionId, 1000);
        // Timeout to start the third block
        multiMsgCard2 = setTimeout(() => {
          // Step 3: Final block with "Success" message
          Block.standard(cardSectionId, {
            backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)'
          });

          let initialMessageCard2 = `<div class="px-12 py-3 bg-success text-white">Success</div>`;
          let notiflixBlock = document.querySelector('#card-block .notiflix-block');
          if (notiflixBlock) notiflixBlock.innerHTML = initialMessageCard2;

          multiMsgCard3 = setTimeout(() => {
            Block.remove(cardSectionId); // Remove the final block
          }, 1610); // Adjust the timeout for the final block
        }, 1610); // Adjust the timeout for the second block
      }, 1610); // Adjust the timeout for the first block
    });
  }

  // List of all button selectors
  const cardButtonSelectors = [
    '.btn-card-block',
    '.btn-card-block-overlay',
    '.btn-card-block-spinner',
    '.btn-card-block-custom',
    '.btn-card-block-multiple'
  ];

  // Select all buttons based on their individual classes
  const cardButtons = cardButtonSelectors.map(selector => document.querySelector(selector));
  // Add click event listener to each button
  cardButtons.forEach(button => {
    if (button) {
      button.addEventListener('click', () => {
        removeCardBtn.style.position = 'relative';
        removeCardBtn.style.pointerEvents = 'auto';
        removeCardBtn.style.zIndex = 1074;
      });
    }
  });
  if (removeCardBtn) {
    removeCardBtn.addEventListener('click', () => {
      setTimeout(() => {
        if (document.querySelector(`${cardSectionId} .notiflix-block`)) {
          Block.remove(cardSectionId);
        } else {
          alert('No active block to remove.');
        }
      }, 400);
      clearTimeout(multiMsgCard1);
      clearTimeout(multiMsgCard2);
      clearTimeout(multiMsgCard3);
    });
  }
  // Blocking with multiple options
  // --------------------------------------------------------------------

  // Default
  if (optionSection && optionBtn) {
    optionBtn.addEventListener('click', () => {
      Block.standard(optionSectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '40px',
        svgColor: config.colors.white
      });
    });
  }

  // hourglass
  if (optionSection && optionBtnHourglass) {
    optionBtnHourglass.addEventListener('click', () => {
      Block.hourglass(optionSectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '40px',
        svgColor: config.colors.white
      });
    });
  }

  // circle
  if (optionSection && optionBtnCircle) {
    optionBtnCircle.addEventListener('click', () => {
      Block.circle(optionSectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '40px',
        svgColor: config.colors.white
      });
    });
  }

  // arrows
  if (optionSection && optionBtnArrows) {
    optionBtnArrows.addEventListener('click', () => {
      Block.arrows(optionSectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '40px',
        svgColor: config.colors.white
      });
    });
  }

  // Dots
  if (optionSection && optionBtnDots) {
    optionBtnDots.addEventListener('click', () => {
      Block.dots(optionSectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '40px',
        svgColor: config.colors.white
      });
    });
  }

  // Pulse
  if (optionSection && optionBtnPulse) {
    optionBtnPulse.addEventListener('click', () => {
      Block.pulse(optionSectionId, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '40px',
        svgColor: config.colors.white
      });
    });
  }

  // List of all button selectors
  const optionButtonSelectors = [
    '.btn-option-block',
    '.btn-option-block-overlay',
    '.btn-option-block-spinner',
    '.btn-option-block-custom',
    '.btn-option-block-multiple'
  ];

  // Select all buttons based on their individual classes
  const optionButtons = optionButtonSelectors.map(selector => document.querySelector(selector));
  // Add click event listener to each button
  optionButtons.forEach(button => {
    if (button) {
      button.addEventListener('click', () => {
        removeOptionBtn.style.position = 'relative';
        removeOptionBtn.style.pointerEvents = 'auto';
        removeOptionBtn.style.zIndex = 1074;
      });
    }
  });
  if (removeOptionBtn) {
    removeOptionBtn.addEventListener('click', () => {
      if (document.querySelector(`${optionSectionId} .notiflix-block`)) {
        Block.remove(optionSectionId);
      } else {
        alert('No active block to remove.');
      }
    });
  }

  // Page Blocking
  // --------------------------------------------------------------------

  // default
  if (pageBtn) {
    pageBtn.addEventListener('click', () => {
      Loading.circle({
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '40px',
        svgColor: config.colors.white
      });
    });
  }

  // Overlay Color
  if (pageBtnOverlay) {
    pageBtnOverlay.addEventListener('click', () => {
      Loading.standard({
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('white-rgb') + ', 0.8)',
        svgSize: '0px'
      });

      const customSpinner = document.createElement('div');
      customSpinner.classList.add('spinner-border', 'text-primary');
      customSpinner.setAttribute('role', 'status');

      let notiflixBlock = document.querySelector('.notiflix-loading');
      notiflixBlock.appendChild(customSpinner);
    });
  }

  // Custom Spinner
  if (pageBtnSpinner) {
    pageBtnSpinner.addEventListener('click', () => {
      Loading.standard({
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '0px'
      });
      let customSpinnerHTML = `
          <div class="sk-wave mx-auto">
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
          </div>
        `;
      let notiflixBlock = document.querySelector('.notiflix-loading');
      notiflixBlock.innerHTML = customSpinnerHTML;
    });
  }

  // Custom Message
  if (pageBtnCustom) {
    pageBtnCustom.addEventListener('click', () => {
      Loading.standard({
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '0px'
      });
      let customSpinnerHTML = `
          <div class="d-flex">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
          </div>
        `;
      let notiflixBlock = document.querySelector('.notiflix-loading');
      notiflixBlock.innerHTML = customSpinnerHTML;
    });
  }

  // Multiple Message
  let multiMsgPage1, multiMsgPage2, multiMsgPage3;
  if (pageBtnMultiple) {
    pageBtnMultiple.addEventListener('click', () => {
      // Step 1: Initial block with spinner and "Please wait..." message
      Loading.standard({
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '0px'
      });

      // Inject custom spinner and message
      let initialMessage = `
            <div class="d-flex justify-content-center">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
            </div>
          `;
      let notiflixBlock = document.querySelector('.notiflix-loading');
      if (notiflixBlock) notiflixBlock.innerHTML = initialMessage;

      // remove the first block
      Loading.remove(1000);
      // Timeout to start the second block
      multiMsgPage1 = setTimeout(() => {
        // Step 2: Second block with "Almost Done..." message
        Loading.standard('Almost Done...', {
          backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
          messageFontSize: '15px',
          svgSize: '0px',
          messageColor: config.colors.white
        });

        // remove the second block
        Loading.remove(1000);
        // Timeout to start the third block
        multiMsgPage2 = setTimeout(() => {
          // Step 3: Final block with "Success" message
          Loading.standard({
            backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)'
          });

          let initialMessage2 = `<div class="px-12 py-3 bg-success text-white">Success</div>`;
          let notiflixBlock = document.querySelector('.notiflix-loading');
          if (notiflixBlock) notiflixBlock.innerHTML = initialMessage2;

          multiMsgPage3 = setTimeout(() => {
            Loading.remove(); // Remove the final block
          }, 1610); // Adjust the timeout for the final block
        }, 1610); // Adjust the timeout for the second block
      }, 1610); // Adjust the timeout for the first block
    });
  }

  // List of all button selectors
  const pageButtonSelectors = [
    '.btn-page-block',
    '.btn-page-block-overlay',
    '.btn-page-block-spinner',
    '.btn-page-block-custom',
    '.btn-page-block-multiple'
  ];

  // Select all buttons based on their individual classes
  const pageButtons = pageButtonSelectors.map(selector => document.querySelector(selector));
  // Add click event listener to each button
  pageButtons.forEach(button => {
    if (button) {
      button.addEventListener('click', () => {
        removePageBtn.style.position = 'relative';
        removePageBtn.style.pointerEvents = 'auto';
        removePageBtn.style.zIndex = 9999;
      });
    }
  });
  if (removePageBtn) {
    removePageBtn.addEventListener('click', () => {
      if (document.querySelector(`.notiflix-loading`)) {
        Loading.remove();
      } else {
        alert('No active loading to remove.');
      }
      clearTimeout(multiMsgPage1);
      clearTimeout(multiMsgPage2);
      clearTimeout(multiMsgPage3);
    });
  }
  // Form Blocking
  // --------------------------------------------------------------------

  // Default
  if (formSection && formBtn) {
    formBtn.addEventListener('click', () => {
      Block.circle(formSectionClass, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '40px',
        svgColor: config.colors.white
      });
    });
  }

  // Overlay Color
  if (formSection && formBtnOverlay) {
    formBtnOverlay.addEventListener('click', () => {
      Block.standard(formSectionClass, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('white-rgb') + ', 0.8)',
        svgSize: '0px'
      });

      let customSpinner = document.createElement('div');
      customSpinner.classList.add('spinner-border', 'text-primary');
      customSpinner.setAttribute('role', 'status');

      let notiflixBlock = document.querySelector('.form-block .notiflix-block');
      notiflixBlock.appendChild(customSpinner);
    });
  }

  // Custom Spinner
  if (formSection && formBtnSpinner) {
    formBtnSpinner.addEventListener('click', () => {
      Block.standard(formSectionClass, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '0px'
      });
      let customSpinnerHTML = `
          <div class="sk-wave mx-auto">
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
          </div>
        `;
      let notiflixBlock = document.querySelector('.form-block .notiflix-block');
      notiflixBlock.innerHTML = customSpinnerHTML;
    });
  }

  // Custom Message
  if (formSection && formBtnCustom) {
    formBtnCustom.addEventListener('click', () => {
      Block.standard(formSectionClass, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '0px'
      });
      let customSpinnerHTML = `
          <div class="d-flex">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
          </div>
        `;
      let notiflixBlock = document.querySelector('.form-block .notiflix-block');
      notiflixBlock.innerHTML = customSpinnerHTML;
    });
  }

  // Multiple Message
  let multiMsgForm1, multiMsgForm2, multiMsgForm3;
  if (formSection && formBtnMultiple) {
    formBtnMultiple.addEventListener('click', () => {
      // Step 1: Initial block with spinner and "Please wait..." message
      Block.standard(formSectionClass, {
        backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
        svgSize: '0px'
      });

      // Inject custom spinner and message
      let initialMessage = `
            <div class="d-flex justify-content-center">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
            </div>
          `;
      let notiflixBlock = document.querySelector('.form-block .notiflix-block');
      if (notiflixBlock) notiflixBlock.innerHTML = initialMessage;

      // remove the first block
      Block.remove(formSectionClass, 1000);
      // Timeout to start the second block
      multiMsgForm1 = setTimeout(() => {
        // Step 2: Second block with "Almost Done..." message
        Block.standard(formSectionClass, 'Almost Done...', {
          backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)',
          messageFontSize: '15px',
          svgSize: '0px',
          messageColor: config.colors.white
        });

        // remove the second block
        Block.remove(formSectionClass, 1000);
        // Timeout to start the third block
        multiMsgForm2 = setTimeout(() => {
          // Step 3: Final block with "Success" message
          Block.standard(formSectionClass, {
            backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)'
          });

          let initialMessage = `<div class="px-12 py-3 bg-success text-white">Success</div>`;
          let notiflixBlock = document.querySelector('.form-block .notiflix-block');
          if (notiflixBlock) notiflixBlock.innerHTML = initialMessage;

          multiMsgForm3 = setTimeout(() => {
            Block.remove(formSectionClass); // Remove the final block
            setTimeout(() => {
              formBtn.classList.remove('disabled');
              formBtnOverlay.classList.remove('disabled');
              formBtnSpinner.classList.remove('disabled');
              formBtnCustom.classList.remove('disabled');
              formBtnMultiple.classList.remove('disabled');
            }, 500);
          }, 1810); // Adjust the timeout for the final block
        }, 1810); // Adjust the timeout for the second block
      }, 1610); // Adjust the timeout for the first block
    });
  }

  // List of all button selectors
  const formButtonSelectors = [
    '.btn-form-block',
    '.btn-form-block-overlay',
    '.btn-form-block-spinner',
    '.btn-form-block-custom',
    '.btn-form-block-multiple'
  ];

  // Select all buttons based on their individual classes
  const formButtons = formButtonSelectors.map(selector => document.querySelector(selector));

  // Add click event listener to each button
  formButtons.forEach(button => {
    if (button) {
      button.addEventListener('click', () => {
        formButtons.forEach(btn => {
          if (btn) {
            btn.classList.add('disabled');
          }
        });
      });
    }
  });

  if (removeFormBtn) {
    removeFormBtn.addEventListener('click', () => {
      setTimeout(() => {
        if (document.querySelector(`${formSectionClass} .notiflix-block`)) {
          Block.remove(formSectionClass);
        } else {
          alert('No active block to remove.');
        }
      }, 450);
      clearTimeout(multiMsgForm1);
      clearTimeout(multiMsgForm2);
      clearTimeout(multiMsgForm3);
      setTimeout(() => {
        formBtn.classList.remove('disabled');
        formBtnOverlay.classList.remove('disabled');
        formBtnSpinner.classList.remove('disabled');
        formBtnCustom.classList.remove('disabled');
        formBtnMultiple.classList.remove('disabled');
      }, 500);
    });
  }
});
