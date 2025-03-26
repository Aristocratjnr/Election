/**
 * Page auth two steps
 */
'use strict';

document.addEventListener('DOMContentLoaded', function () {
  // Self-executing function to initialize event listeners and form validation
  (() => {
    const maskWrapper = document.querySelector('.numeral-mask-wrapper');
    const twoStepsForm = document.querySelector('#twoStepsForm');

    if (maskWrapper) {
      // Loop through each child of the mask wrapper
      Array.from(maskWrapper.children).forEach(pin => {
        pin.addEventListener('keyup', e => {
          // Only handle numeric keys or backspace
          if (/^\d$/.test(e.key)) {
            // Move focus to the next field if maxlength is reached
            if (pin.nextElementSibling && pin.value.length === parseInt(pin.getAttribute('maxlength'))) {
              pin.nextElementSibling.focus();
            }
          } else if (e.key === 'Backspace') {
            // Move focus to the previous field on backspace
            if (pin.previousElementSibling) {
              pin.previousElementSibling.focus();
            }
          }
        });

        pin.addEventListener('keypress', e => {
          // Prevent entering the minus key
          if (e.key === '-') {
            e.preventDefault();
          }
        });
      });
    }

    // Form validation for OTP
    if (twoStepsForm) {
      const numeralMaskList = twoStepsForm.querySelectorAll('.numeral-mask');

      // Keyup handler to update OTP value
      const keyupHandler = () => {
        let otpComplete = true;
        let otpValue = '';

        numeralMaskList.forEach(maskElement => {
          if (maskElement.value === '') {
            otpComplete = false;
          }
          otpValue += maskElement.value;
        });

        twoStepsForm.querySelector('[name="otp"]').value = otpComplete ? otpValue : '';
      };

      numeralMaskList.forEach(maskElement => {
        maskElement.addEventListener('keyup', keyupHandler);
      });

      // Initialize form validation if FormValidation is defined
      if (typeof FormValidation !== 'undefined') {
        FormValidation.formValidation(twoStepsForm, {
          fields: {
            otp: {
              validators: {
                notEmpty: {
                  message: 'Please enter OTP'
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
            defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
            autoFocus: new FormValidation.plugins.AutoFocus()
          }
        });
      }
    }
  })();
});
