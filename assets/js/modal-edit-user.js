/**
 * Edit User
 */

'use strict';

// Select2 (jquery)
$(function () {
  const select2 = $('.select2');

  // Select2 Country
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select value',
        dropdownParent: $this.parent()
      });
    });
  }
});

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    // variables
    const modalEditUserTaxID = document.querySelector('.modal-edit-tax-id');
    const modalEditUserPhone = document.querySelector('.phone-number-mask');

    // Prefix
    if (modalEditUserTaxID) {
      const prefixOption = {
        prefix: 'TIN',
        blocks: [3, 3, 3, 4],
        delimiter: ' '
      };
      registerCursorTracker({
        input: modalEditUserTaxID,
        delimiter: ' '
      });
      modalEditUserTaxID.value = formatGeneral('', prefixOption);
      modalEditUserTaxID.addEventListener('input', event => {
        modalEditUserTaxID.value = formatGeneral(event.target.value, prefixOption);
      });
    }

    // Phone Number Input Mask
    if (modalEditUserPhone) {
      modalEditUserPhone.addEventListener('input', event => {
        const cleanValue = event.target.value.replace(/\D/g, '');
        modalEditUserPhone.value = formatGeneral(cleanValue, {
          blocks: [3, 3, 4],
          delimiters: [' ', ' ']
        });
      });
      registerCursorTracker({
        input: modalEditUserPhone,
        delimiter: ' '
      });
    }
  })();
});
