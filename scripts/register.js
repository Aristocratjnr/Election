'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const registerForm = document.querySelector('#registerForm');
  if (!registerForm) return;

  const submitButton = registerForm.querySelector('button[type="submit"]');
  const signupText = document.getElementById('signupText');
  const signupSpinner = document.getElementById('signupSpinner');
  const dobField = document.getElementById('dob');
  const contactField = document.getElementById('contact');

  // Function to set loading state
  function setLoadingState(isLoading) {
    if (isLoading) {
      signupText.textContent = 'Signing up...';
      signupSpinner.classList.remove('d-none');
    } else {
      signupText.textContent = 'Sign up';
      signupSpinner.classList.add('d-none');
    }
  }

  // Initialize Form Validation
  if (typeof FormValidation !== 'undefined') {
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

    // Debounce function for input fields
    function debounce(func, delay = 300) {
      let timer;
      return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
      };
    }

    // Format DOB input (YYYY-MM-DD)
    if (dobField) {
      dobField.addEventListener('input', debounce(function (e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value.length > 4 ? 
          value.slice(0, 4) + '-' + value.slice(4, 6) + (value.length > 6 ? '-' + value.slice(6, 8) : '') 
          : value;
      }));
    }

    // Format contact number with +233 for Ghana
    if (contactField) {
      contactField.addEventListener('input', debounce(function (e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = '+233 ' + (value.startsWith('233') ? value.slice(3) : value);
      }));
    }

    // Handle Registration Form Submit
    registerForm.addEventListener('submit', function (e) {
      e.preventDefault();
      setLoadingState(true);

      validation.validate().then(function (status) {
        if (status === 'Valid') {
          const formData = new FormData(registerForm);

          fetch('signUpAuth.php', {
            method: 'POST',
            body: formData
          })
            .then(response => response.json())
            .then(data => {
              if (data.status === 'success') {
                window.location.href = 'register-success.php?message=' + encodeURIComponent(data.message);
              } else {
                alert(data.message);
                setLoadingState(false);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('An error occurred. Please try again later.');
              setLoadingState(false);
            });
        } else {
          setLoadingState(false);
        }
      });
    });

    // Reset button state if form validation fails
    registerForm.addEventListener('invalid', function(e) {
      setLoadingState(false);
    }, true);
  }
});