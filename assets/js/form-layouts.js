/**
 * Form Layout Vertical
 */
'use strict';

(function () {
  const phoneMaskList = document.querySelectorAll('.phone-mask'),
    creditCardMask = document.querySelector('.credit-card-mask'),
    expiryDateMask = document.querySelector('.expiry-date-mask'),
    cvvMask = document.querySelector('.cvv-code-mask'),
    datepickerList = document.querySelectorAll('.dob-picker'),
    formCheckInputPayment = document.querySelectorAll('.form-check-input-payment');

  // Phone Number
  if (phoneMaskList) {
    phoneMaskList.forEach(function (phoneMask) {
      phoneMask.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        phoneMask.value = formatGeneral(cleanValue, {
          blocks: [3, 3, 4],
          delimiters: [' ', ' ']
        });
      });
      registerCursorTracker({
        input: phoneMask,
        delimiter: ' '
      });
    });
  }

  // Credit Card
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

  // Flat Picker Birth Date
  if (datepickerList) {
    datepickerList.forEach(function (datepicker) {
      datepicker.flatpickr({
        monthSelectorType: 'static'
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
})();

// select2 (jquery)
$(function () {
  // Init custom option check
  window.Helpers.initCustomOptionCheck();

  // Select2 Country
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select value',
        dropdownParent: $this.parent()
      });
    });
  }
});
