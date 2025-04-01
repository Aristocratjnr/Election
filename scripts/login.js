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
      }
    });

    // Handle Authentication Form Submit
    formAuthentication.addEventListener('submit', function (e) {
      e.preventDefault();
      validation.validate().then(function (status) {
        if (status === 'Valid') {
          const formData = new FormData(formAuthentication);

          $.ajax({
            url: 'signInAuth.php',
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
});
