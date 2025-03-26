/**
 * UI Toasts
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  // Bootstrap toasts example
  // --------------------------------------------------------------------
  const toastAnimationExample = document.querySelector('.toast-ex'),
    toastPlacementExample = document.querySelector('.toast-placement-ex'),
    toastAnimationBtn = document.querySelector('#showToastAnimation'),
    toastPlacementBtn = document.querySelector('#showToastPlacement');
  let selectedType, selectedAnimation, selectedPlacement, toast, toastAnimation, toastPlacement;

  // Animation Button click
  if (toastAnimationBtn) {
    toastAnimationBtn.onclick = function () {
      if (toastAnimation) {
        toastDispose(toastAnimation);
      }
      selectedType = document.querySelector('#selectType').value;
      selectedAnimation = document.querySelector('#selectAnimation').value;

      toastAnimationExample.classList.add(selectedType, selectedAnimation);
      toastAnimation = new bootstrap.Toast(toastAnimationExample);
      toastAnimation.show();
    };
  }

  // Dispose toast when open another
  function toastDispose(toast) {
    if (toast && toast._element !== null) {
      if (toastPlacementExample) {
        toastPlacementExample.classList.remove(selectedType);
        DOMTokenList.prototype.remove.apply(toastPlacementExample.classList, selectedPlacement);
      }
      if (toastAnimationExample) {
        toastAnimationExample.classList.remove(selectedType, selectedAnimation);
      }
      toast.dispose();
    }
  }
  // Placement Button click
  if (toastPlacementBtn) {
    toastPlacementBtn.onclick = function () {
      if (toastPlacement) {
        toastDispose(toastPlacement);
      }
      selectedType = document.querySelector('#selectTypeOpt').value;
      selectedPlacement = document.querySelector('#selectPlacement').value.split(' ');

      toastPlacementExample.classList.add(selectedType);
      DOMTokenList.prototype.add.apply(toastPlacementExample.classList, selectedPlacement);
      toastPlacement = new bootstrap.Toast(toastPlacementExample);
      toastPlacement.show();
    };
  }

  //notyf (js)
  // --------------------------------------------------------------------

  // Initialize message index
  let i = -1;

  // Function to cycle through messages
  const getMessage = function () {
    const msgs = [
      "Don't be pushed around by the fears in your mind. Be led by the dreams in your heart.",
      '<div class="mb-3"><input class="input-small form-control" value="Textbox"/>&nbsp;<a href="http://example.com" target="_blank" class="text-white">This is a hyperlink</a></div><div class="d-flex"><button type="button" id="okBtn" class="btn btn-primary btn-sm me-2">Close me</button><button type="button" id="surpriseBtn" class="btn btn-sm btn-secondary">Surprise me</button></div>',
      'Live the Life of Your Dreams',
      'Believe in Yourself!',
      'Be mindful. Be grateful. Be positive.',
      'Accept yourself, love yourself!'
    ];
    i = (i + 1) % msgs.length;
    return msgs[i];
  };

  // Custom Notyf class to allow HTML content in messages
  class CustomNotyf extends Notyf {
    _renderNotification(options) {
      const notification = super._renderNotification(options);

      // Replace textContent with innerHTML to render HTML content
      if (options.message) {
        notification.message.innerHTML = options.message;
      }

      return notification;
    }
  }

  // Initialize CustomNotyf instance with default behaviors
  const notyf = new CustomNotyf({
    duration: 3000,
    ripple: true,
    dismissible: false,
    position: { x: 'right', y: 'top' },
    types: [
      {
        type: 'info',
        background: config.colors.info,
        className: 'notyf__info',
        icon: {
          className: 'icon-base bx bxs-info-circle icon-md text-white',
          tagName: 'i'
        }
      },
      {
        type: 'warning',
        background: config.colors.warning,
        className: 'notyf__warning',
        icon: {
          className: 'icon-base bx bxs-error icon-md text-white',
          tagName: 'i'
        }
      },
      {
        type: 'success',
        background: config.colors.success,
        className: 'notyf__success',
        icon: {
          className: 'icon-base bx bxs-check-circle icon-md text-white',
          tagName: 'i'
        }
      },
      {
        type: 'error',
        background: config.colors.danger,
        className: 'notyf__error',
        icon: {
          className: 'icon-base bx bxs-x-circle icon-md text-white',
          tagName: 'i'
        }
      }
    ]
  });

  // Event listener for Show Notification button
  document.getElementById('showNotification').addEventListener('click', () => {
    const message = document.getElementById('message').value || getMessage(); // Use getMessage() to get the next message
    const dismissible = document.getElementById('dismissible').checked;
    const ripple = document.getElementById('ripple').checked;
    const durationInput = document.getElementById('duration').value;
    const duration = durationInput ? parseInt(durationInput) : 3000;

    // Get selected position
    const positionYValue = document.querySelector('input[name="positiony"]:checked').value;
    const positionXValue = document.querySelector('input[name="positionx"]:checked').value;
    const position = { x: positionXValue, y: positionYValue };

    // Get selected notification type
    const type = document.querySelector('input[name="notificationType"]:checked').value;

    // Build the notification options
    const notificationOptions = {
      type: type,
      message: message,
      duration: duration,
      dismissible: dismissible,
      ripple: ripple,
      position: position
    };

    // Display notification and get the reference
    attachNotificationEventListeners();
    notyf.open(notificationOptions);
  });

  // Event listener for Clear Notifications button
  document.getElementById('clearNotifications').addEventListener('click', () => {
    notyf.dismissAll();
  });

  // Function to attach event listeners to elements inside the notification
  function attachNotificationEventListeners() {
    // Wait for the DOM to update
    setTimeout(() => {
      const okBtn = document.getElementById('okBtn');
      const surpriseBtn = document.getElementById('surpriseBtn');

      if (okBtn) {
        okBtn.addEventListener('click', () => {
          notyf.dismissAll(); // Close all notifications
        });
      }

      if (surpriseBtn) {
        surpriseBtn.addEventListener('click', () => {
          notyf.success('Surprise! This is a new message.');
        });
      }
    }, 100);
  }
});
