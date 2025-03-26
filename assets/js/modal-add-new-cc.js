/**
 * Add new credit card
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    // Variables
    const creditCardMask = document.querySelector('.credit-card-mask'),
      expiryDateMask = document.querySelector('.expiry-date-mask'),
      cvvMask = document.querySelector('.cvv-code-mask'),
      btnReset = document.querySelector('.btn-reset');

    if (creditCardMask) {
      creditCardMask.addEventListener('input', event => {
        creditCardMask.value = formatCreditCard(event.target.value);
        const cleanValue = event.target.value.replace(/\D/g, '');
        let type = getCreditCardType(cleanValue);
        if (type && type !== 'unknown' && type !== 'general') {
          document.querySelector('.card-type').innerHTML =
            '<img src="' + assetsPath + 'img/icons/payments/' + type + '-cc.png" class="cc-icon-image" height="20"/>';
        } else {
          document.querySelector('.card-type').innerHTML = '';
        }
      });
      registerCursorTracker({
        input: creditCardMask,
        delimiter: ' '
      });
      // reset card image on click of cancel
      btnReset.addEventListener('click', function (e) {
        // blank '.card-type' innerHTML to remove image
        document.querySelector('.card-type').innerHTML = '';
      });
    }

    // Expiry Date Mask
    if (expiryDateMask) {
      expiryDateMask.addEventListener('input', event => {
        expiryDateMask.value = formatDate(event.target.value, {
          date: true,
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

    // Credit card form validation
    FormValidation.formValidation(document.getElementById('addNewCCForm'), {
      fields: {
        modalAddCard: {
          validators: {
            notEmpty: {
              message: 'Please enter your credit card number'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          // eleInvalidClass: '',
          eleValidClass: '',
          rowSelector: '.form-control-validation'
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        // Submit the form when all fields are valid
        // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      },
      init: instance => {
        instance.on('plugins.message.placed', function (e) {
          //* Move the error message out of the `input-group` element
          if (e.element.parentElement.classList.contains('input-group')) {
            e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
          }
        });
      }
    }).on('plugins.message.displayed', function (e) {
      if (e.element.parentElement.classList.contains('input-group')) {
        //* Move the error message out of the `input-group` element
        e.element.parentElement.insertAdjacentElement('afterend', e.messageElement.parentElement);
      }
    });
  })();
});
