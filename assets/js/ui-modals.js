/**
 * UI Modals
 */

'use strict';

(function () {
  // Animation Dropdown
  const animationDropdown = document.querySelector('#animation-dropdown'),
    animationModal = document.querySelector('#animationModal');
  if (animationDropdown) {
    animationDropdown.onchange = function () {
      animationModal.classList = '';
      animationModal.classList.add('modal', 'animate__animated', this.value);
    };
  }

  // On hiding modal, remove iframe video/audio to stop playing
  const youTubeModal = document.querySelector('#youTubeModal'),
    youTubeModalVideo = youTubeModal.querySelector('iframe');
  youTubeModal.addEventListener('hidden.bs.modal', function () {
    youTubeModalVideo.setAttribute('src', '');
  });

  // Function to get and auto play youTube video
  const autoPlayYouTubeModal = function () {
    const modalTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="modal"]'));
    modalTriggerList.map(function (modalTriggerEl) {
      modalTriggerEl.onclick = function () {
        const theModal = this.getAttribute('data-bs-target'),
          videoSRC = this.getAttribute('data-theVideo'),
          videoSRCauto = `${videoSRC}?autoplay=1`,
          modalVideo = document.querySelector(`${theModal} iframe`);
        if (modalVideo) {
          modalVideo.setAttribute('src', videoSRCauto);
        }
      };
    });
  };

  // Calling function on load
  autoPlayYouTubeModal();

  // Onboarding modal carousel height animation
  document.querySelectorAll('.carousel').forEach(carousel => {
    carousel.addEventListener('slide.bs.carousel', event => {
      // Ensure next slide exists
      if (!event.relatedTarget) {
        console.error('Next slide not found');
        return;
      }
      // Use requestAnimationFrame to wait for render
      requestAnimationFrame(() => {
        // Force reflow
        void event.relatedTarget.offsetHeight;
        const nextHeight = Math.max(
          event.relatedTarget.offsetHeight,
          event.relatedTarget.scrollHeight,
          event.relatedTarget.getBoundingClientRect().height
        );
        const carouselParent = carousel.querySelector('.active.carousel-item').parentElement;
        // Animate only if we have valid heights
        if (nextHeight > 0 && carouselParent) {
          carouselParent.animate([{ height: carouselParent.offsetHeight + 'px' }, { height: nextHeight + 'px' }], {
            duration: 500,
            easing: 'ease',
            fill: 'forwards'
          });
        }
      });
    });
  });
})();
