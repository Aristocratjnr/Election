/**
 * Two Factor Authentication
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const phoneMaskList = document.querySelectorAll('#twoFactorAuthInputSms');

    // Phone Number
    if (phoneMaskList) {
      phoneMaskList.forEach(function (phoneMask) {
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
      });
    }
  })();
});
