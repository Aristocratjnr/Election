/**
 * Add Payment Offcanvas
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  // Invoice amount
  const paymentAmount = document.querySelector('.invoice-amount');

  if (paymentAmount) {
    paymentAmount.addEventListener('input', event => {
      paymentAmount.value = formatNumeral(event.target.value, {
        numeralThousandsGroupStyle: 'thousand'
      });
    });
  }
  // Datepicker
  const date = new Date(),
    invoiceDateList = document.querySelectorAll('.invoice-date');

  if (invoiceDateList) {
    invoiceDateList.forEach(function (invoiceDateEl) {
      invoiceDateEl.flatpickr({
        monthSelectorType: 'static',
        defaultDate: date,
        static: true
      });
    });
  }
});
