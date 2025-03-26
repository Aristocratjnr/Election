/**
 * Form Extras
 */

'use strict';
document.addEventListener('DOMContentLoaded', function (e) {
  const creditCard = document.querySelector('.credit-card-mask'),
    phoneMask = document.querySelector('.phone-number-mask'),
    dateMask = document.querySelector('.date-mask'),
    timeMask = document.querySelector('.time-mask'),
    numeralMask = document.querySelector('.numeral-mask'),
    blockMask = document.querySelector('.block-mask'),
    delimiterMask = document.querySelector('.delimiter-mask'),
    customDelimiter = document.querySelector('.custom-delimiter-mask'),
    prefixMask = document.querySelector('.prefix-mask');

  // Cleave Zen Input Mask
  // --------------------------------------------------------------------

  // Credit Card
  if (creditCard) {
    creditCard.addEventListener('input', event => {
      creditCard.value = formatCreditCard(event.target.value);
      const cleanValue = event.target.value.replace(/\D/g, '');
      let cardType = getCreditCardType(cleanValue);
      if (cardType && cardType !== 'unknown' && cardType !== 'general') {
        document.querySelector('.card-type').innerHTML =
          `<img src="${assetsPath}img/icons/payments/${cardType}-cc.png" height="20"/>`;
      } else {
        document.querySelector('.card-type').innerHTML = '';
      }
    });
    registerCursorTracker({
      input: creditCard,
      delimiter: ' '
    });
  }

  // Phone Number
  if (phoneMask) {
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
  }

  // Date
  if (dateMask) {
    dateMask.addEventListener('input', event => {
      dateMask.value = formatDate(event.target.value, {
        delimiter: '-',
        datePattern: ['Y', 'm', 'd']
      });
    });
    registerCursorTracker({
      input: dateMask,
      delimiter: '-'
    });
  }

  // Time
  if (timeMask) {
    timeMask.addEventListener('input', event => {
      timeMask.value = formatTime(event.target.value, {
        timePattern: ['h', 'm', 's']
      });
    });
    registerCursorTracker({
      input: timeMask,
      delimiter: ':'
    });
  }

  // Numeral
  if (numeralMask) {
    numeralMask.addEventListener('input', event => {
      numeralMask.value = formatNumeral(event.target.value, {
        numeralThousandsGroupStyle: 'thousand'
      });
    });
  }

  // Block
  if (blockMask) {
    blockMask.addEventListener('input', event => {
      blockMask.value = formatGeneral(event.target.value, {
        blocks: [4, 3, 3],
        delimiters: [' ', ' ']
      });
    });
    registerCursorTracker({
      input: blockMask,
      delimiter: ' '
    });
  }

  // Delimiter
  if (delimiterMask) {
    delimiterMask.addEventListener('input', event => {
      delimiterMask.value = formatGeneral(event.target.value, {
        blocks: [3, 3, 3],
        delimiter: '·'
      });
    });
    registerCursorTracker({
      input: delimiterMask,
      delimiter: '.'
    });
  }

  // Custom Delimiter
  if (customDelimiter) {
    customDelimiter.addEventListener('input', event => {
      customDelimiter.value = formatGeneral(event.target.value, {
        delimiters: ['.', '.', '-'],
        blocks: [3, 3, 3, 2],
        uppercase: true
      });
    });
    registerCursorTracker({
      input: customDelimiter,
      delimiters: ['.', '-']
    });
  }

  // Prefix
  if (prefixMask) {
    const prefixOption = {
      prefix: '+63',
      blocks: [3, 3, 3, 4],
      delimiter: ' '
    };
    registerCursorTracker({
      input: prefixMask,
      delimiter: ' '
    });
    prefixMask.value = formatGeneral('', prefixOption);
    prefixMask.addEventListener('input', event => {
      prefixMask.value = formatGeneral(event.target.value, prefixOption);
    });
  }

  const inputMaxLength = document.getElementById('maxLength-example1').getAttribute('maxlength');
  const inputInfo = document.getElementById('input-maxlength-info');
  const inputElement = document.getElementById('maxLength-example1');

  // Set initial character count
  window.Helpers.maxLengthCount(inputElement, inputInfo, inputMaxLength);

  // Add event listener to update the character count
  inputElement.addEventListener('input', function () {
    window.Helpers.maxLengthCount(inputElement, inputInfo, inputMaxLength);
  });

  const textareaMaxLength = document.getElementById('maxLength-example2').getAttribute('maxlength');
  const textareaInfo = document.getElementById('textarea-maxlength-info');
  const textareaElement = document.getElementById('maxLength-example2');

  window.Helpers.maxLengthCount(textareaElement, textareaInfo, textareaMaxLength);

  textareaElement.addEventListener('input', function () {
    window.Helpers.maxLengthCount(textareaElement, textareaInfo, textareaMaxLength);
  });

  // Form Repeater
  // ! Using jQuery each loop to add dynamic id and class for inputs. You may need to improve it based on form fields.
  // -----------------------------------------------------------------------------------------------------------------

  const formRepeater = $('.form-repeater');
  if (formRepeater.length) {
    var row = 2;
    var col = 1;
    formRepeater.on('submit', function (e) {
      e.preventDefault();
    });
    formRepeater.repeater({
      show: function () {
        var fromControl = $(this).find('.form-control, .form-select');
        var formLabel = $(this).find('.form-label');

        fromControl.each(function (i) {
          var id = 'form-repeater-' + row + '-' + col;
          $(fromControl[i]).attr('id', id);
          $(formLabel[i]).attr('for', id);
          col++;
        });

        row++;

        $(this).slideDown();
      },
      hide: function (e) {
        confirm('Are you sure you want to delete this element?') && $(this).slideUp(e);
      }
    });
  }
});
