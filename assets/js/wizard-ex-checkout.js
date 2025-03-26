/**
 *  Form Wizard
 */

'use strict';

// star rating
document.addEventListener('DOMContentLoaded', function (e) {
  let r = parseInt(window.Helpers.getCssVar('gray-200', true).slice(1, 3), 16);
  let g = parseInt(window.Helpers.getCssVar('gray-200', true).slice(3, 5), 16);
  let b = parseInt(window.Helpers.getCssVar('gray-200', true).slice(5, 7), 16);
  const readOnlyRating = document.querySelectorAll('.read-only-ratings');
  const fullStarSVG =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' %3E%3Cpath fill='%23FFD700' d='M21.947 9.179a1 1 0 0 0-.868-.676l-5.701-.453l-2.467-5.461a.998.998 0 0 0-1.822-.001L8.622 8.05l-5.701.453a1 1 0 0 0-.619 1.713l4.213 4.107l-1.49 6.452a1 1 0 0 0 1.53 1.057L12 18.202l5.445 3.63a1.001 1.001 0 0 0 1.517-1.106l-1.829-6.4l4.536-4.082c.297-.268.406-.686.278-1.065'/%3E%3C/svg%3E";
  const emptyStarSVG =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' %3E%3Cpath fill='rgb(" +
    r +
    ',' +
    g +
    ',' +
    b +
    ")' d='M21.947 9.179a1 1 0 0 0-.868-.676l-5.701-.453l-2.467-5.461a.998.998 0 0 0-1.822-.001L8.622 8.05l-5.701.453a1 1 0 0 0-.619 1.713l4.213 4.107l-1.49 6.452a1 1 0 0 0 1.53 1.057L12 18.202l5.445 3.63a1.001 1.001 0 0 0 1.517-1.106l-1.829-6.4l4.536-4.082c.297-.268.406-.686.278-1.065'/%3E%3C/svg%3E";
  readOnlyRating.forEach(element => {
    let ratings = new Raty(element, {
      starOn: fullStarSVG,
      starOff: emptyStarSVG
    });
    ratings.init();
  });
});

(function () {
  // Init custom option check
  window.Helpers.initCustomOptionCheck();

  // libs
  const creditCardMask = document.querySelector('.credit-card-mask'),
    expiryDateMask = document.querySelector('.expiry-date-mask'),
    cvvMask = document.querySelector('.cvv-code-mask');

  // Credit Card
  if (creditCardMask) {
    creditCardMask.addEventListener('input', event => {
      creditCardMask.value = formatCreditCard(event.target.value);
      const cleanValue = event.target.value.replace(/\D/g, '');
      let cardType = getCreditCardType(cleanValue);
      if (cardType && cardType !== 'unknown' && cardType !== 'general') {
        document.querySelector('.card-type').innerHTML =
          `<img src="${assetsPath}img/icons/payments/${cardType}-cc.png" height="19"/>`;
      } else {
        document.querySelector('.card-type').innerHTML = '';
      }
    });
    registerCursorTracker({
      input: creditCardMask,
      delimiter: ' '
    });
  }
  // Expiry Date Mask
  if (expiryDateMask) {
    expiryDateMask.addEventListener('input', event => {
      expiryDateMask.value = formatDate(event.target.value, {
        delimiter: '/',
        datePattern: ['m', 'y']
      });
    });
    registerCursorTracker({
      input: expiryDateMask,
      delimiter: '-'
    });
  }

  // CVV
  if (cvvMask) {
    cvvMask.addEventListener('input', event => {
      cvvMask.value = formatNumeral(event.target.value, {
        numeralThousandsGroupStyle: 'thousand'
      });
    });
  }

  // Wizard Checkout
  // --------------------------------------------------------------------
  const wizardCheckout = document.querySelector('#wizard-checkout'),
    wizardCheckoutBtnNextList = [].slice.call(wizardCheckout.querySelectorAll('.btn-next')),
    wizardCheckoutBtnSubmit = wizardCheckout.querySelector('.btn-submit');

  if (typeof wizardCheckout !== undefined && wizardCheckout !== null) {
    const numberedStepper = new Stepper(wizardCheckout, {
      linear: false
    });
    if (wizardCheckoutBtnNextList) {
      wizardCheckoutBtnNextList.forEach(wizardCheckoutBtnNext => {
        wizardCheckoutBtnNext.addEventListener('click', event => {
          numberedStepper.next();
        });
      });
    }
    if (wizardCheckoutBtnSubmit) {
      wizardCheckoutBtnSubmit.addEventListener('click', event => {
        alert('Submitted..!!');
      });
    }
  }
})();
