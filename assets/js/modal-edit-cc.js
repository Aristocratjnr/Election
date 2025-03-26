/**
 * Edit credit card
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const editCreditCardMaskEdit = document.querySelector('.credit-card-mask-edit'),
      editExpiryDateMaskEdit = document.querySelector('.expiry-date-mask-edit'),
      editCVVMaskEdit = document.querySelector('.cvv-code-mask-edit');

    // Credit Card
    if (editCreditCardMaskEdit) {
      editCreditCardMaskEdit.addEventListener('input', event => {
        editCreditCardMaskEdit.value = formatCreditCard(event.target.value);
        const cleanValue = event.target.value.replace(/\D/g, '');
        let cardType = getCreditCardType(cleanValue);
        if (cardType && cardType !== 'unknown' && cardType !== 'general') {
          document.querySelector('.card-type-edit').innerHTML =
            `<img src="${assetsPath}img/icons/payments/${cardType}-cc.png" height="20"/>`;
        } else {
          document.querySelector('.card-type-edit').innerHTML = '';
        }
      });
      registerCursorTracker({
        input: editCreditCardMaskEdit,
        delimiter: ' '
      });
    }

    // Expiry Date MaskEdit
    if (editExpiryDateMaskEdit) {
      editExpiryDateMaskEdit.addEventListener('input', event => {
        editExpiryDateMaskEdit.value = formatDate(event.target.value, {
          delimiter: '/',
          datePattern: ['m', 'y']
        });
      });
      registerCursorTracker({
        input: editExpiryDateMaskEdit,
        delimiter: '/'
      });
    }

    // CVV MaskEdit
    if (editCVVMaskEdit) {
      editCVVMaskEdit.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        editCVVMaskEdit.value = formatNumeral(cleanValue, {
          numeral: true,
          numeralPositiveOnly: true
        });
      });
    }

    // Credit card form validation
    FormValidation.formValidation(document.getElementById('editCCForm'), {
      fields: {
        modalEditCard: {
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
    });
  })();
});
