/**
 * Clipboard
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  const clipboardList = [].slice.call(document.querySelectorAll('.clipboard-btn'));

  const notyf = new Notyf({
    duration: 3000,
    dismissible: true,
    position: { x: 'right', y: 'top' }
  });
  if (ClipboardJS) {
    clipboardList.map(function (clipboardEl) {
      const clipboard = new ClipboardJS(clipboardEl);
      clipboard.on('success', function (e) {
        if (e.action === 'copy') {
          notyf.success('Copied to Clipboard!!');
        }
      });
    });
  } else {
    clipboardList.map(function (clipboardEl) {
      clipboardEl.setAttribute('disabled', true);
    });
  }
});
