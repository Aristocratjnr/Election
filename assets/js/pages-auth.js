'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const formAuthentication = document.querySelector('#formAuthentication');

  if (formAuthentication && typeof FormValidation !== 'undefined') {
    const validation = FormValidation.formValidation(formAuthentication, {
      fields: {
        studentID: {
          validators: {
            notEmpty: {
              message: 'Please enter student ID'
            },
            stringLength: {
              min: 6,
              message: 'Student ID must be more than 6 characters'
            }
          }
        },
        email: {
          validators: {
            notEmpty: {
              message: 'Please enter your email'
            },
            emailAddress: {
              message: 'Please enter a valid email address'
            }
          }
        },
        'email-username': {
          validators: {
            notEmpty: {
              message: 'Please enter email'
            },
            stringLength: {
              min: 6,
              message: 'Username must be more than 6 characters'
            }
          }
        },
        password: {
          validators: {
            notEmpty: {
              message: 'Please enter your password'
            },
            stringLength: {
              min: 6,
              message: 'Password must be more than 6 characters'
            }
          }
        },
        confirmPassword: {
          validators: {
            notEmpty: {
              message: 'Please confirm password'
            },
            identical: {
              compare: () => formAuthentication.querySelector('[name="password"]').value,
              message: 'The password and its confirmation do not match'
            },
            stringLength: {
              min: 6,
              message: 'Password must be more than 6 characters'
            }
          }
        },
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: '.form-control-validation'
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      },
      init: instance => {
        instance.on('plugins.message.placed', e => {
          if (e.element.parentElement.classList.contains('input-group')) {
            e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
          }
        });
      }
    });

    // Custom form submission
    formAuthentication.addEventListener('submit', function (e) {
      e.preventDefault();

      validation.validate().then(function (status) {
        if (status === 'Valid') {
          const formData = new FormData(formAuthentication);

          $.ajax({
            url: 'controllers/app.php?action=register',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            error: function (err) {
              console.log(err);
            },
            success: function (resp) {
              const response = JSON.parse(resp);

              if (response.status === 'success') {
                location.href = response.redirect_url;
              } else {
                $('#formAuthentication .alert').remove(); // Clear previous alerts

                const alert = `<div class="alert alert-danger">${response.message}</div>`;
                $('#formAuthentication').prepend(alert);

                if (response.status === 'student_id') {
                  formAuthentication.querySelector('[name="student_id"]').classList.add('is-invalid');
                } else {
                  formAuthentication.querySelector('[name="password"]').classList.add('is-invalid');
                }

                formAuthentication.querySelector('button[type="submit"]').disabled = false;
              }
            }
          });
        }
      });
    });
  }

  // Two Steps Verification: Format numeral inputs
  const numeralMaskElements = document.querySelectorAll('.numeral-mask');
  const formatNumeral = value => value.replace(/\D/g, '');

  if (numeralMaskElements.length > 0) {
    numeralMaskElements.forEach(numeralMaskEl => {
      numeralMaskEl.addEventListener('input', event => {
        numeralMaskEl.value = formatNumeral(event.target.value);
      });
    });
  }
});
