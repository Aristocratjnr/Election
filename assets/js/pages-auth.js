'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const formAuthentication = document.querySelector('#formAuthentication');
  const registerForm = document.querySelector('#registerForm');
  
  // Function to format contact number with +233
  const formatContactNumber = (value) => {
    value = value.replace(/\D/g, '');  // Remove all non-numeric characters
    if (value.length <= 9) {
      return '+233 ' + value;
    } else {
      return '+233 ' + value.substring(0, 9); // Limit to 9 digits after +233
    }
  };

  if (formAuthentication && typeof FormValidation !== 'undefined') {
    const validation = FormValidation.formValidation(formAuthentication, {
      fields: {
        student: {
          validators: {
            notEmpty: { message: 'Please enter student ID' },
            stringLength: { min: 6, message: 'Student ID must be more than 6 characters' }
          }
        },
        password: {
          validators: {
            notEmpty: { message: 'Please enter your password' },
            stringLength: { min: 6, message: 'Password must be more than 6 characters' }
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
            url: 'controllers/app.php?action=login',
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

  if (registerForm && typeof FormValidation !== 'undefined') {
    const validation = FormValidation.formValidation(registerForm, {
      fields: {
        student: {
          validators: {
            notEmpty: { message: 'Please enter student ID' },
            stringLength: { min: 6, message: 'Student ID must be more than 6 characters' }
          }
        },
        name: {
          validators: {
            notEmpty: { message: 'Please enter your name' },
            stringLength: { min: 3, message: 'Name must be more than 3 characters' }
          }
        },
        email: {
          validators: {
            notEmpty: { message: 'Please enter your email' },
            emailAddress: { message: 'Please enter a valid email address' }
          }
        },
        department: {
          validators: {
            notEmpty: { message: 'Please enter your department' },
            stringLength: { min: 3, message: 'Department must be more than 3 characters' }
          }
        },
        dob: {
          validators: {
            notEmpty: { message: 'Please enter your date of birth' },
            date: { format: 'YYYY-MM-DD', message: 'The date of birth is not valid' }
          }
        },
        contact: {
          validators: {
            notEmpty: { message: 'Please enter your contact number' },
            stringLength: {
              min: 13,
              max: 13,
              message: 'Contact number must be in the format +233XXXXXXXXX'
            },
            regexp: {
              regexp: /^\+233\d{9}$/,
              message: 'Please enter a valid Ghanaian contact number starting with +233'
            }
          }
        },
        password: {
          validators: {
            notEmpty: { message: 'Please enter your password' },
            stringLength: { min: 6, message: 'Password must be more than 6 characters' }
          }
        },
        confirmPassword: {
          validators: {
            notEmpty: { message: 'Please confirm password' },
            identical: {
              compare: () => registerForm.querySelector('[name="password"]').value,
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
      }
    });

    // Handle Registration Form Submit
    registerForm.addEventListener('submit', function (e) {
      e.preventDefault();
      validation.validate().then(function (status) {
        if (status === 'Valid') {
          const formData = new FormData(registerForm);

          $.ajax({
            url: 'controllers/app.php?action=register',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (resp) {
              try {
                const response = JSON.parse(resp);

                $('#registerForm .alert').remove(); // Clear previous alerts

                if (response.status === 'success') {
                  location.href = response.redirect_url;
                } else {
                  const alert = `<div class="alert alert-danger">${response.message}</div>`;
                  $('#registerForm').prepend(alert);
                }
              } catch (err) {
                console.error("Response parsing error", err);
              }
            },
            error: function (err) {
              console.error("AJAX Error", err);
              const alert = `<div class="alert alert-danger">An error occurred. Please try again later.</div>`;
              $('#registerForm').prepend(alert);
            }
          });
        }
      });
    });
  }

  // Contact number input event
  const contactFields = document.querySelectorAll('.contact-number');
  contactFields.forEach(field => {
    field.addEventListener('input', function (e) {
      e.target.value = formatContactNumber(e.target.value);
    });
  });
});
