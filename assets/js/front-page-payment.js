'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  // Variables
  const billingZipCode = document.querySelector('.billings-zip-code'),
    creditCardMask = document.querySelector('.billing-card-mask'),
    expiryDateMask = document.querySelector('.billing-expiry-date-mask'),
    cvvMask = document.querySelector('.billing-cvv-mask'),
    formCheckInputPayment = document.querySelectorAll('.form-check-input-payment');

  // Pincode
  if (billingZipCode) {
    billingZipCode.addEventListener('input', event => {
      billingZipCode.value = formatNumeral(event.target.value, {
        delimiter: '',
        numeral: true
      });
    });
  }

  if (creditCardMask) {
    creditCardMask.addEventListener('input', event => {
      creditCardMask.value = formatCreditCard(event.target.value);
      const cleanValue = event.target.value.replace(/\D/g, '');
      let cardType = getCreditCardType(cleanValue);
      if (cardType && cardType !== 'unknown' && cardType !== 'general') {
        document.querySelector('.card-type').innerHTML =
          `<img src="${assetsPath}img/icons/payments/${cardType}-cc.png" height="20"/>`;
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
      delimiter: '/'
    });
  }

  // CVV
  if (cvvMask) {
    cvvMask.addEventListener('input', event => {
      const cleanValue = event.target.value.replace(/\D/g, '');
      cvvMask.value = formatNumeral(cleanValue, {
        numeral: true,
        numeralPositiveOnly: true
      });
    });
  }

  // Toggle CC Payment Method based on selected option
  if (formCheckInputPayment) {
    formCheckInputPayment.forEach(function (paymentInput) {
      paymentInput.addEventListener('change', function (e) {
        const paymentInputValue = e.target.value;
        if (paymentInputValue === 'credit-card') {
          document.querySelector('#form-credit-card').classList.remove('d-none');
        } else {
          document.querySelector('#form-credit-card').classList.add('d-none');
        }
      });
    });
  }
});
