'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const formAuthentication = document.querySelector('#formAuthentication');

  if (formAuthentication && typeof FormValidation !== 'undefined') {
    const validation = FormValidation.formValidation(formAuthentication, {
      fields: {
        student: {
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
        name: {
          validators: {
            notEmpty: {
              message: 'Please enter your name'
            },
            stringLength: {
              min: 3,
              message: 'Name must be more than 3 characters'
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
        department: {
          validators: {
            notEmpty: {
              message: 'Please enter your department'
            },
            stringLength: {
              min: 3,
              message: 'Department must be more than 3 characters'
            }
          }
        },
        contact: {
          validators: {
            notEmpty: {
              message: 'Please enter your contact number'
            },
            stringLength: {
              min: 10,
              message: 'Contact number must be more than 10 digits'
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

    // Handle Form Submit After Validation
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
            success: function (resp) {
              try {
                const response = JSON.parse(resp);

                $('#formAuthentication .alert').remove(); // Clear previous alerts

                if (response.status === 'success') {
                  location.href = response.redirect_url;
                } else {
                  const alert = `<div class="alert alert-danger">${response.message}</div>`;
                  $('#formAuthentication').prepend(alert);

                  if (response.status === 'student_id') {
                    formAuthentication.querySelector('[name="student"]').classList.add('is-invalid');
                  } else {
                    formAuthentication.querySelector('[name="password"]').classList.add('is-invalid');
                  }
                }
              } catch (err) {
                console.error("Response parsing error", err);
              }
            },
            error: function (err) {
              console.error("AJAX Error", err);
              const alert = `<div class="alert alert-danger">An error occurred. Please try again later.</div>`;
              $('#formAuthentication').prepend(alert);
            }
          });
        }
      });
    });
  }

  // Format numeric inputs (e.g., verification code fields)
  const numeralMaskElements = document.querySelectorAll('.numeral-mask');
  const formatNumeral = value => value.replace(/\D/g, '');

  if (numeralMaskElements.length > 0) {
    numeralMaskElements.forEach(el => {
      el.addEventListener('input', event => {
        el.value = formatNumeral(event.target.value);
      });
    });
  }
});
