/**
 * App Email
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  (function () {
    // Utility function to initialize PerfectScrollbar
    const initPerfectScrollbar = (element, options = { wheelPropagation: false, suppressScrollX: true }) => {
      if (element) new PerfectScrollbar(element, options);
    };

    // Query selectors for elements and collections
    const selectors = {
      emailList: document.querySelector('.email-list'),
      emailListItems: Array.from(document.querySelectorAll('.email-list-item')),
      emailListItemInputs: Array.from(document.querySelectorAll('.email-list-item-input')),
      emailView: document.querySelector('.app-email-view-content'),
      emailFilters: document.querySelector('.email-filters'),
      emailFilterByFolders: Array.from(document.querySelectorAll('.email-filter-folders li')),
      emailEditor: document.querySelector('.email-editor'),
      appEmailSidebar: document.querySelector('.app-email-sidebar'),
      appOverlay: document.querySelector('.app-overlay'),
      emailReplyEditor: document.querySelector('.email-reply-editor'),
      bookmarkEmail: Array.from(document.querySelectorAll('.email-list-item-bookmark')),
      selectAllEmails: document.getElementById('email-select-all'),
      emailSearch: document.querySelector('.email-search-input'),
      toggleCC: document.querySelector('.email-compose-toggle-cc'),
      toggleBCC: document.querySelector('.email-compose-toggle-bcc'),
      emailCompose: document.querySelector('.app-email-compose'),
      emailListDelete: document.querySelector('.email-list-delete'),
      emailListRead: document.querySelector('.email-list-read'),
      emailListEmpty: document.querySelector('.email-list-empty'),
      refreshEmails: document.querySelector('.email-refresh'),
      emailViewContainer: document.getElementById('app-email-view'),
      emailFilterFolderLists: Array.from(document.querySelectorAll('.email-filter-folders li')),
      emailListItemActions: Array.from(document.querySelectorAll('.email-list-item-actions li'))
    };

    // Initialize scrollbars where needed
    initPerfectScrollbar(selectors.emailList);
    initPerfectScrollbar(selectors.emailFilters);
    initPerfectScrollbar(selectors.emailView);

    // Utility function to initialize Quill Editor
    const initQuillEditor = (selector, toolbar) => {
      if (selector) {
        new Quill(selector, {
          modules: { toolbar },
          placeholder: 'Write your message...',
          theme: 'snow'
        });
      }
    };

    // Initialize editors
    initQuillEditor(selectors.emailEditor, '.email-editor-toolbar');
    initQuillEditor(selectors.emailReplyEditor, '.email-reply-toolbar');

    // Bookmark email functionality
    selectors.bookmarkEmail.forEach(emailItem => {
      emailItem.addEventListener('click', e => {
        const emailItemParent = e.currentTarget.closest('.email-list-item');
        e.stopPropagation();

        if (emailItemParent.hasAttribute('data-starred')) {
          // If attribute exists, remove it
          emailItemParent.removeAttribute('data-starred');
        } else {
          // If attribute does not exist, set it with the value "true"
          emailItemParent.setAttribute('data-starred', 'true');
        }
      });
    });

    // Select all functionality
    if (selectors.selectAllEmails) {
      selectors.selectAllEmails.addEventListener('click', e => {
        selectors.emailListItemInputs.forEach(input => {
          input.checked = e.currentTarget.checked;
        });
      });
    }

    // Select single email and update 'Select All' checkbox state
    if (selectors.emailListItemInputs) {
      selectors.emailListItemInputs.forEach(emailListItemInput => {
        emailListItemInput.addEventListener('click', e => {
          e.stopPropagation();

          // Count checked inputs
          const checkedCount = selectors.emailListItemInputs.filter(input => input.checked).length;
          const totalInputs = selectors.emailListItemInputs.length;

          // Update 'Select All' checkbox and indeterminate state
          selectors.selectAllEmails.indeterminate = checkedCount > 0 && checkedCount < totalInputs;
          selectors.selectAllEmails.checked = checkedCount === totalInputs;
        });
      });
    }

    // Search emails based on input text
    if (selectors.emailSearch) {
      selectors.emailSearch.addEventListener('keyup', e => {
        const searchValue = e.currentTarget.value.toLowerCase();
        const activeFolderFilter = document.querySelector('.email-filter-folders .active');
        const selectedFolder = activeFolderFilter ? activeFolderFilter.getAttribute('data-target') : 'inbox';

        // Filter emails based on the active folder
        const emailListItems =
          selectedFolder !== 'inbox'
            ? Array.from(document.querySelectorAll(`.email-list-item[data-${selectedFolder}="true"]`))
            : selectors.emailListItems;

        // Show/hide emails based on the search term
        emailListItems.forEach(emailItem => {
          const itemText = emailItem.textContent.toLowerCase();
          emailItem.classList.toggle('d-block', itemText.includes(searchValue));
          emailItem.classList.toggle('d-none', !itemText.includes(searchValue));
        });
      });
    }

    // Filter emails based on folder type (Inbox, Sent, Draft, etc.)
    selectors.emailFilterByFolders.forEach(folder => {
      folder.addEventListener('click', e => {
        const targetFolder = e.currentTarget.getAttribute('data-target');

        // Hide sidebar and overlay
        selectors.appEmailSidebar.classList.remove('show');
        selectors.appOverlay.classList.remove('show');

        // Update active class for folder filters
        selectors.emailFilterByFolders.forEach(f => f.classList.remove('active'));
        e.currentTarget.classList.add('active');

        // Filter email items based on selected folder
        selectors.emailListItems.forEach(emailItem => {
          const matchesFolder = targetFolder === 'inbox' || emailItem.hasAttribute(`data-${targetFolder}`);
          emailItem.classList.toggle('d-block', matchesFolder);
          emailItem.classList.toggle('d-none', !matchesFolder);
        });
      });
    });

    // Toggle visibility of CC/BCC input fields
    const toggleVisibility = selector => {
      document.querySelector(selector).classList.toggle('d-block');
      document.querySelector(selector).classList.toggle('d-none');
    };

    if (selectors.toggleBCC) {
      selectors.toggleBCC.addEventListener('click', () => toggleVisibility('.email-compose-bcc'));
    }

    if (selectors.toggleCC) {
      selectors.toggleCC.addEventListener('click', () => toggleVisibility('.email-compose-cc'));
    }

    // Clear compose email message inputs when modal is hidden
    selectors.emailCompose.addEventListener('hidden.bs.modal', () => {
      document.querySelector('.email-editor .ql-editor').innerHTML = '';
      document.getElementById('emailContacts').value = '';
      initSelect2();
    });

    // Delete selected emails
    if (selectors.emailListDelete) {
      selectors.emailListDelete.addEventListener('click', () => {
        selectors.emailListItemInputs.forEach(input => {
          if (input.checked) {
            input.closest('li.email-list-item').remove();
          }
        });
        selectors.selectAllEmails.indeterminate = false;
        selectors.selectAllEmails.checked = false;

        // Show empty message if no emails are left
        if (selectors.emailListItems.length === 0) {
          selectors.emailListEmpty.classList.remove('d-none');
        }
      });
    }

    // Mark selected emails as read
    if (selectors.emailListRead) {
      selectors.emailListRead.addEventListener('click', () => {
        selectors.emailListItemInputs.forEach(input => {
          if (input.checked) {
            input.checked = false;
            const emailItem = input.closest('li.email-list-item');
            emailItem.classList.add('email-marked-read');

            // Update envelope icon
            const emailActions = emailItem.querySelector('.email-list-item-actions li');
            emailActions.classList.replace('email-read', 'email-unread');
            const icon = emailActions.querySelector('i');
            icon.classList.replace('bx-envelope-open', 'bx-envelope');
          }
        });
        selectors.selectAllEmails.indeterminate = false;
        selectors.selectAllEmails.checked = false;
      });
    }

    // Refresh emails with loading animation
    if (selectors.refreshEmails && selectors.emailList) {
      const emailListClass = '.email-list';
      const emailListInstance = new PerfectScrollbar(selectors.emailList, {
        wheelPropagation: false,
        suppressScrollX: true
      });

      selectors.refreshEmails.addEventListener('click', () => {
        // Block the email list section with Notiflix
        Block.standard(emailListClass, {
          backgroundColor: 'rgba(' + window.Helpers.getCssVar('black-rgb') + ', 0.1)',
          svgSize: '0px'
        });

        // Add custom spinner to the Notiflix block
        const customSpinner = document.createElement('div');
        customSpinner.classList.add('spinner-border', 'text-primary');
        customSpinner.setAttribute('role', 'status');

        const notiflixBlock = document.querySelector('.email-list .notiflix-block');
        if (notiflixBlock) {
          notiflixBlock.appendChild(customSpinner);
        }

        // Simulate a timeout and unblock
        setTimeout(() => {
          // Disable vertical scroll suppression
          emailListInstance.settings.suppressScrollY = false;
          // Unblock the section
          Block.remove(emailListClass);
        }, 1000);

        // Suppress scroll during the block
        emailListInstance.settings.suppressScrollY = true;
      });
    }

    // Toggle visibility of earlier messages

    const earlierMsg = document.querySelector('.email-earlier-msgs');

    if (earlierMsg) {
      earlierMsg.addEventListener('click', () => {
        const emailCardLast = document.querySelector('.email-card-last');
        const emailCardPrev = earlierMsg.nextElementSibling;

        if (emailCardLast) emailCardLast.classList.add('hide-pseudo');

        // Vanilla JavaScript slideToggle effect
        if (emailCardPrev) {
          emailCardPrev.style.display =
            emailCardPrev.style.display === 'none' || !emailCardPrev.style.display ? 'block' : 'none';
          emailCardPrev.classList.toggle('slide-toggle');
        }

        // Remove the earlier message link after expanding
        earlierMsg.remove();
      });
    }

    // Email contacts (select2)
    // ? Using jquery vars due to select2 jQuery dependency
    let emailContacts = $('#emailContacts');
    function initSelect2() {
      if (emailContacts.length) {
        function renderContactsAvatar(option) {
          if (!option.id) {
            return option.text;
          }
          let $avatar =
            "<div class='d-flex flex-wrap align-items-center'>" +
            "<div class='avatar avatar-xs me-2 w-px-20 h-px-20'>" +
            "<img src='" +
            assetsPath +
            'img/avatars/' +
            $(option.element).data('avatar') +
            "' alt='avatar' class='rounded-circle' />" +
            '</div>' +
            option.text +
            '</div>';

          return $avatar;
        }
        emailContacts.wrap('<div class="position-relative"></div>').select2({
          placeholder: 'Select value',
          dropdownParent: emailContacts.parent(),
          closeOnSelect: false,
          templateResult: renderContactsAvatar,
          templateSelection: renderContactsAvatar,
          escapeMarkup: function (es) {
            return es;
          }
        });
      }
    }
    initSelect2();

    // Scroll to bottom on reply click
    const emailViewContent = document.querySelector('.app-email-view-content');
    const scrollToReplyButton = emailViewContent ? emailViewContent.querySelector('.scroll-to-reply') : null;

    if (scrollToReplyButton && emailViewContent) {
      scrollToReplyButton.addEventListener('click', () => {
        if (emailViewContent.scrollTop === 0) {
          // Smooth scroll animation to the bottom
          emailViewContent.scrollTo({
            top: emailViewContent.scrollHeight,
            behavior: 'smooth'
          });
        }
      });
    }

    // Close view on email filter folder list click
    if (selectors.emailFilterFolderLists) {
      selectors.emailFilterFolderLists.forEach(folder => {
        folder.addEventListener('click', () => {
          selectors.emailViewContainer.classList.remove('show');
        });
      });
    }

    // Email List Items Actions
    if (selectors.emailListItemActions) {
      selectors.emailListItemActions.forEach(action => {
        action.addEventListener('click', e => {
          e.stopPropagation();
          const emailItem = action.closest('li.email-list-item');

          if (action.classList.contains('email-delete')) {
            // Delete email item
            emailItem.remove();

            // Show empty message if no emails are left
            if (!document.querySelectorAll('.email-list-item').length) {
              selectors.emailListEmpty.classList.remove('d-none');
            }
          } else if (action.classList.contains('email-read') || action.classList.contains('email-unread')) {
            // Toggle read/unread state
            const icon = action.querySelector('i');

            emailItem.classList.toggle('email-marked-read', action.classList.contains('email-read'));
            action.classList.toggle('email-read');
            action.classList.toggle('email-unread');
            icon.classList.toggle('bx-envelope-open');
            icon.classList.toggle('bx-envelope');
          }
        });
      });
    }
  })();
});
