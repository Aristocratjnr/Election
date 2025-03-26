/**
 * Cards Actions
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const collapseElementList = Array.from(document.querySelectorAll('.card-collapsible'));
  const expandElementList = Array.from(document.querySelectorAll('.card-expand'));
  const closeElementList = Array.from(document.querySelectorAll('.card-close'));
  const cardDnD = document.getElementById('sortable-4');

  // Collapsible card
  // --------------------------------------------------------------------
  collapseElementList.forEach(function (collapseElement) {
    collapseElement.addEventListener('click', function (event) {
      event.preventDefault();
      // Collapse the element
      new bootstrap.Collapse(collapseElement.closest('.card').querySelector('.collapse'));
      // Toggle collapsed class in `.card-header` element
      collapseElement.closest('.card-header').classList.toggle('collapsed');
      // Toggle class bx-chevron-down & bx-chevron-up
      Helpers._toggleClass(collapseElement.firstElementChild, 'bx-chevron-down', 'bx-chevron-up');
    });
  });

  // Card Toggle fullscreen
  // --------------------------------------------------------------------
  expandElementList.forEach(function (expandElement) {
    expandElement.addEventListener('click', function (event) {
      event.preventDefault();
      // Toggle class bx-fullscreen & bx-exit-fullscreen
      Helpers._toggleClass(expandElement.firstElementChild, 'bx-fullscreen', 'bx-exit-fullscreen');
      expandElement.closest('.card').classList.toggle('card-fullscreen');
    });
  });

  // Toggle fullscreen on ESC key
  document.addEventListener('keyup', function (event) {
    event.preventDefault();
    //Esc button
    if (event.key === 'Escape') {
      const cardFullscreen = document.querySelector('.card-fullscreen');
      // Toggle class bx-fullscreen & bx-exit-fullscreen

      if (cardFullscreen) {
        Helpers._toggleClass(
          cardFullscreen.querySelector('.card-expand').firstElementChild,
          'bx-fullscreen',
          'bx-exit-fullscreen'
        );
        cardFullscreen.classList.toggle('card-fullscreen');
      }
    }
  });

  // Card close
  // --------------------------------------------------------------------
  closeElementList.forEach(function (closeElement) {
    closeElement.addEventListener('click', function (event) {
      event.preventDefault();
      closeElement.closest('.card').classList.add('d-none');
    });
  });

  // Sortable.js (Drag & Drop cards)
  // --------------------------------------------------------------------
  if (cardDnD) {
    Sortable.create(cardDnD, {
      animation: 500,
      handle: '.card'
    });
  }

  // Card reload (js)
  // --------------------------------------------------------------------
  const cardReload = document.querySelectorAll('.card-reload');

  if (cardReload) {
    // Add unique data attributes to each card
    const cards = document.querySelectorAll('.card-action');
    cards.forEach((card, index) => {
      card.dataset.cardId = `card-${index + 1}`;
    });

    cardReload.forEach(button => {
      button.addEventListener('click', function (e) {
        e.preventDefault();

        // Find the closest card with the "card-action" class
        const card = button.closest('.card-action');
        if (!card) {
          console.error('Closest card with .card-action class not found!');
          return;
        }

        // Get the unique identifier for the card
        const cardId = card.dataset.cardId;

        // Apply Notiflix Block to the specific card
        Block.standard(`[data-card-id="${cardId}"]`, {
          backgroundColor:
            document.documentElement.getAttribute('data-bs-theme') === 'dark'
              ? 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.5)'
              : 'rgba(' + window.Helpers.getCssVar('white-rgb') + ', 0.5)',
          svgSize: '0px'
        });

        // Inject custom spinner HTML into the blocked card
        const customSpinnerHTML = `
          <div class="sk-fold sk-primary">
            <div class="sk-fold-cube"></div>
            <div class="sk-fold-cube"></div>
            <div class="sk-fold-cube"></div>
            <div class="sk-fold-cube"></div>
          </div>
          <h5>LOADING...</h5>
        `;
        const notiflixBlock = card.querySelector('.notiflix-block');
        if (notiflixBlock) {
          notiflixBlock.innerHTML = customSpinnerHTML;
        }

        // Simulate an async operation and unblock the card
        setTimeout(function () {
          Block.remove(`[data-card-id="${cardId}"]`);

          // Check if a card alert exists, and add the alert message
          const cardAlert = card.querySelector('.card-alert');
          if (cardAlert) {
            cardAlert.innerHTML = `
              <div class="alert alert-solid-danger alert-dismissible fade show" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <span class="fw-medium">Holy grail!</span> Your success/error message here.
              </div>
            `;
          }
        }, 2500); // Adjust the timeout duration as needed
      });
    });
  }
});
