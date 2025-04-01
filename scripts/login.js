'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const formAuthentication = document.querySelector('#formAuthentication');

  if (formAuthentication) {
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

    formAuthentication.addEventListener('submit', function (e) {
      e.preventDefault();
      validation.validate().then(function (status) {
        if (status === 'Valid') {
          const formData = new FormData(formAuthentication);
          const submitButton = formAuthentication.querySelector('button[type="submit"]');
          const originalButtonHTML = submitButton.innerHTML;

          // Set loading state
          submitButton.disabled = true;
          submitButton.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Signing in...
          `;

          $.ajax({
            url: 'signInAuth.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json' // Let jQuery parse the JSON automatically
          })
          .done(function(response) {
            // response is already parsed as JSON by jQuery
            $('#formAuthentication .alert').remove();
            
            if (response && response.status === 'success') {
              window.location.href = response.redirect_url || 'dashboard.php';
            } else {
              const message = response?.message || 'Login failed. Please try again.';
              showAlert('danger', message);
            }
          })
          .fail(function(xhr) {
            let message = 'An error occurred during login';
            
            // Try to get error message from response
            if (xhr.responseJSON && xhr.responseJSON.message) {
              message = xhr.responseJSON.message;
            } else if (xhr.responseText) {
              message = xhr.responseText;
            }
            
            showAlert('danger', message);
          })
          .always(function() {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonHTML;
          });
        }
      });
    });

    function showAlert(type, message) {
      const alertDiv = document.createElement('div');
      alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
      alertDiv.role = 'alert';
      alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
      formAuthentication.prepend(alertDiv);
      
      // Auto-dismiss after 5 seconds
      setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
      }, 5000);
    }
  }
});