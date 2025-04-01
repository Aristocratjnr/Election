'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const registerForm = document.querySelector('#registerForm');
  const submitButton = registerForm.querySelector('button[type="submit"]');

  // Initialize form validation
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
            stringLength: { min: 10, max: 15, message: 'Contact number must be 10 excluding the country code' }
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
        bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector: '.form-control-validation' }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });

    
    // Validate and format DOB input (YYYY-MM-DD)
    const dobField = document.getElementById('dob');
    if (dobField) {
      dobField.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        let formattedValue = '';

        if (value.length > 0) {
          formattedValue += value.substring(0, 4);
          if (value.length > 4) {
            formattedValue += '-' + value.substring(4, 6);
          }
          if (value.length > 6) {
            formattedValue += '-' + value.substring(6, 8);
          }
        }

        e.target.value = formattedValue;
      });
    }

    // Format contact number with +233 for Ghana
    const contactField = document.querySelector('#contact');
    if (contactField) {
      contactField.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove non-digit characters
        if (value.startsWith('233')) {
          value = value.slice(3); // If the user starts with 233, keep it
        }
        e.target.value = '+233 ' + value; // Prepend +233 to the value
      });
    }

    // Handle Registration Form Submit
    registerForm.addEventListener('submit', function (e) {
      e.preventDefault();
      
      validation.validate().then(function (status) {
        if (status === 'Valid') {
          const formData = new FormData(registerForm);

          // Send form data to backend via AJAX
          $.ajax({
            url: 'signUpAuth.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (resp) {
              try {
                const response = typeof resp === 'object' ? resp : JSON.parse(resp);
                const modalMessage = document.getElementById('modalMessage');
                const modalRedirectBtn = document.getElementById('modalRedirectBtn');
                const closeModalBtn = document.getElementById('closeModalBtn');

                // Display response message in modal
                modalMessage.innerHTML = response.message;

                const modal = new bootstrap.Modal(document.getElementById('statusModal'));
                modal.show();

                if (response.status === 'success') {
                  // Show the redirect button and set a timeout to navigate
                  modalRedirectBtn.style.display = 'inline-block';
                  modalRedirectBtn.addEventListener('click', function () {
                    window.location.href = 'login.php';
                  });

                  // Auto-close modal after 3 seconds and redirect
                  setTimeout(() => {
                    modal.hide();
                    window.location.href = 'login.php';
                  }, 3000);
                } else {
                  // If there's an error, show a "close" button
                  modalRedirectBtn.style.display = 'none';
                  closeModalBtn.style.display = 'inline-block';
                }
              } catch (err) {
                console.error('Response parsing error:', err, 'Raw response:', resp);
                document.getElementById('modalMessage').innerHTML = 'Invalid response. Please try again.';
                new bootstrap.Modal(document.getElementById('statusModal')).show();
              }
            },
            error: function (xhr) {
              console.error('AJAX Error:', xhr.responseText);
              document.getElementById('modalMessage').innerHTML = 'An error occurred. Please try again later.';
              new bootstrap.Modal(document.getElementById('statusModal')).show();
            },
            complete: function () {
              submitButton.disabled = false; // Re-enable button after response
            }
          });
        } else {
          submitButton.disabled = false;
        }
      });
    });
  }
});
