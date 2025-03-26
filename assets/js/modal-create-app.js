/**
 *  Modal Example Wizard
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  // Modal id
  const appModal = document.getElementById('createApp');

  // Credit Card
  const creditCardMask1 = document.querySelector('.app-credit-card-mask'),
    expiryDateMask1 = document.querySelector('.app-expiry-date-mask'),
    cvvMask1 = document.querySelector('.app-cvv-code-mask');
  let cleave;

  // Cleave JS card Mask
  setTimeout(() => {
    if (creditCardMask1) {
      creditCardMask1.addEventListener('input', event => {
        let cleanValue = event.target.value.replace(/\D/g, '');
        let cardType = getCreditCardType(cleanValue);
        creditCardMask1.value = formatCreditCard(cleanValue);
        if (cardType && cardType !== 'unknown' && cardType !== 'general') {
          document.querySelector('.app-card-type').innerHTML =
            `<img src="${assetsPath}img/icons/payments/${cardType}-cc.png" height="20"/>`;
        } else {
          document.querySelector('.app-card-type').innerHTML = '';
        }
      });

      registerCursorTracker({
        input: creditCardMask1,
        delimiter: ' '
      });
    }
  }, 200);

  // Expiry Date Mask
  if (expiryDateMask1) {
    expiryDateMask1.addEventListener('input', event => {
      expiryDateMask1.value = formatDate(event.target.value, {
        delimiter: '/',
        datePattern: ['m', 'y']
      });
    });
    registerCursorTracker({
      input: expiryDateMask1,
      delimiter: '/'
    });
  }

  // CVV
  if (cvvMask1) {
    cvvMask1.addEventListener('input', event => {
      const cleanValue = event.target.value.replace(/\D/g, '');
      cvvMask1.value = formatNumeral(cleanValue, {
        numeral: true,
        numeralPositiveOnly: true
      });
    });
  }
  appModal.addEventListener('show.bs.modal', function (event) {
    const wizardCreateApp = document.querySelector('#wizard-create-app');
    if (typeof wizardCreateApp !== undefined && wizardCreateApp !== null) {
      // Wizard next prev button
      const wizardCreateAppNextList = [].slice.call(wizardCreateApp.querySelectorAll('.btn-next'));
      const wizardCreateAppPrevList = [].slice.call(wizardCreateApp.querySelectorAll('.btn-prev'));
      const wizardCreateAppBtnSubmit = wizardCreateApp.querySelector('.btn-submit');

      const createAppStepper = new Stepper(wizardCreateApp, {
        linear: false
      });

      if (wizardCreateAppNextList) {
        wizardCreateAppNextList.forEach(wizardCreateAppNext => {
          wizardCreateAppNext.addEventListener('click', event => {
            createAppStepper.next();
          });
        });
      }
      if (wizardCreateAppPrevList) {
        wizardCreateAppPrevList.forEach(wizardCreateAppPrev => {
          wizardCreateAppPrev.addEventListener('click', event => {
            createAppStepper.previous();
          });
        });
      }

      if (wizardCreateAppBtnSubmit) {
        wizardCreateAppBtnSubmit.addEventListener('click', event => {
          alert('Submitted..!!');
        });
      }
    }
  });
});
