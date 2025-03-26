/**
 * App User View - Security
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  const formChangePass = document.querySelector('#formChangePassword'),
    phoneMask = document.querySelector('.phone-number-mask');

  // Form validation for Change password
  if (formChangePass) {
    const fv = FormValidation.formValidation(formChangePass, {
      fields: {
        newPassword: {
          validators: {
            notEmpty: {
              message: 'Please enter new password'
            },
            stringLength: {
              min: 8,
              message: 'Password must be more than 8 characters'
            }
          }
        },
        confirmPassword: {
          validators: {
            notEmpty: {
              message: 'Please confirm new password'
            },
            identical: {
              compare: function () {
                return formChangePass.querySelector('[name="newPassword"]').value;
              },
              message: 'The password and its confirm are not the same'
            },
            stringLength: {
              min: 8,
              message: 'Password must be more than 8 characters'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
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
          if (e.element.parentElement.classList.contains('input-group')) {
            e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
          }
        });
      }
    });
  }

  // phone number formatting
  if (phoneMask) {
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
  }
});
