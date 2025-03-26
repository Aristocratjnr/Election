/**
 * App Kanban
 */

'use strict';

(async function () {
  let boards;
  const kanbanSidebar = document.querySelector('.kanban-update-item-sidebar'),
    kanbanWrapper = document.querySelector('.kanban-wrapper'),
    commentEditor = document.querySelector('.comment-editor'),
    kanbanAddNewBoard = document.querySelector('.kanban-add-new-board'),
    kanbanAddNewInput = [].slice.call(document.querySelectorAll('.kanban-add-board-input')),
    kanbanAddBoardBtn = document.querySelector('.kanban-add-board-btn'),
    datePicker = document.querySelector('#due-date'),
    select2 = $('.select2'), // ! Using jquery vars due to select2 jQuery dependency
    assetsPath = document.querySelector('html').getAttribute('data-assets-path');

  // Init kanban Offcanvas
  const kanbanOffcanvas = new bootstrap.Offcanvas(kanbanSidebar);

  // Get kanban data
  const kanbanResponse = await fetch(assetsPath + 'json/kanban.json');
  if (!kanbanResponse.ok) {
    console.error('error', kanbanResponse);
  }
  boards = await kanbanResponse.json();

  // datepicker init
  if (datePicker) {
    datePicker.flatpickr({
      monthSelectorType: 'static',
      static: true,
      altInput: true,
      altFormat: 'j F, Y',
      dateFormat: 'Y-m-d'
    });
  }

  //! TODO: Update Event label and guest code to JS once select removes jQuery dependency
  // select2
  if (select2.length) {
    function renderLabels(option) {
      if (!option.id) {
        return option.text;
      }
      var $badge = "<div class='badge " + $(option.element).data('color') + "'> " + option.text + '</div>';
      return $badge;
    }

    select2.each(function () {
      var $this = $(this);
      $this.wrap("<div class='position-relative'></div>").select2({
        placeholder: 'Select Label',
        dropdownParent: $this.parent(),
        templateResult: renderLabels,
        templateSelection: renderLabels,
        escapeMarkup: function (es) {
          return es;
        }
      });
    });
  }

  // Comment editor
  if (commentEditor) {
    new Quill(commentEditor, {
      modules: {
        toolbar: '.comment-toolbar'
      },
      placeholder: 'Write a Comment...',
      theme: 'snow'
    });
  }

  // Render board dropdown
  const renderBoardDropdown = () => `
  <div class="dropdown">
      <i class="dropdown-toggle icon-base bx bx-dots-vertical-rounded cursor-pointer"
         id="board-dropdown"
         data-bs-toggle="dropdown"
         aria-haspopup="true"
         aria-expanded="false">
      </i>
      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="board-dropdown">
          <a class="dropdown-item delete-board" href="javascript:void(0)">
              <i class="icon-base bx bx-trash icon-xs me-1"></i>
              <span class="align-middle">Delete</span>
          </a>
          <a class="dropdown-item" href="javascript:void(0)">
              <i class="icon-base bx bx-rename icon-xs me-1"></i>
              <span class="align-middle">Rename</span>
          </a>
          <a class="dropdown-item" href="javascript:void(0)">
              <i class="icon-base bx bx-archive icon-xs me-1"></i>
              <span class="align-middle">Archive</span>
          </a>
      </div>
  </div>
`;
  // Render item dropdown
  const renderDropdown = () => `
<div class="dropdown kanban-tasks-item-dropdown">
    <i class="dropdown-toggle icon-base bx bx-dots-vertical-rounded"
       id="kanban-tasks-item-dropdown"
       data-bs-toggle="dropdown"
       aria-haspopup="true"
       aria-expanded="false">
    </i>
    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="kanban-tasks-item-dropdown">
        <a class="dropdown-item" href="javascript:void(0)">Copy task link</a>
        <a class="dropdown-item" href="javascript:void(0)">Duplicate task</a>
        <a class="dropdown-item delete-task" href="javascript:void(0)">Delete</a>
    </div>
</div>
`;

  // Render header
  const renderHeader = (color, text) => `
<div class="d-flex justify-content-between flex-wrap align-items-center mb-2">
    <div class="item-badges">
        <div class="badge bg-label-${color}">${text}</div>
    </div>
    ${renderDropdown()}
</div>
`;

  // Render avatar
  const renderAvatar = (images = '', pullUp = false, size = '', margin = '', members = '') => {
    const transitionClass = pullUp ? ' pull-up' : '';
    const sizeClass = size ? `avatar-${size}` : '';
    const memberList = members ? members.split(',') : [];

    return images
      ? images
          .split(',')
          .map((img, index, arr) => {
            const marginClass = margin && index !== arr.length - 1 ? ` me-${margin}` : '';
            const memberName = memberList[index] || '';
            return `
            <div class="avatar ${sizeClass}${marginClass} w-px-26 h-px-26"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="${memberName}">
                <img src="${assetsPath}img/avatars/${img}"
                     alt="Avatar"
                     class="rounded-circle${transitionClass}">
            </div>
        `;
          })
          .join('')
      : '';
  };

  // Render footer
  const renderFooter = (attachments, comments, assigned, members) => `
<div class="d-flex justify-content-between align-items-center flex-wrap mt-2">
    <div class="d-flex">
        <span class="d-flex align-items-center me-2">
            <i class="icon-base bx bx-paperclip me-1"></i>
            <span class="attachments">${attachments}</span>
        </span>
        <span class="d-flex align-items-center ms-2">
            <i class="icon-base bx bx-chat me-1"></i>
            <span>${comments}</span>
        </span>
    </div>
    <div class="avatar-group d-flex align-items-center assigned-avatar">
        ${renderAvatar(assigned, true, 'xs', null, members)}
    </div>
</div>
`;

  // Initialize kanban
  const kanban = new jKanban({
    element: '.kanban-wrapper',
    gutter: '12px',
    widthBoard: '250px',
    dragItems: true,
    boards: boards,
    dragBoards: true,
    addItemButton: true,
    buttonContent: '+ Add Item',
    itemAddOptions: {
      enabled: true,
      content: '+ Add New Item',
      class: 'kanban-title-button btn btn-default',
      footer: false
    },
    click: el => {
      const element = el;
      const title = element.getAttribute('data-eid')
        ? element.querySelector('.kanban-text').textContent
        : element.textContent;
      const date = element.getAttribute('data-due-date');
      const dateObj = new Date();
      const year = dateObj.getFullYear();
      const dateToUse = date
        ? `${date}, ${year}`
        : `${dateObj.getDate()} ${dateObj.toLocaleString('en', { month: 'long' })}, ${year}`;
      const label = element.getAttribute('data-badge-text');
      const avatars = element.getAttribute('data-assigned');

      // Show kanban offcanvas
      kanbanOffcanvas.show();

      // Populate sidebar fields
      kanbanSidebar.querySelector('#title').value = title;
      kanbanSidebar.querySelector('#due-date').nextSibling.value = dateToUse;

      // Using jQuery for select2
      $('.kanban-update-item-sidebar').find(select2).val(label).trigger('change');

      // Remove and update assigned avatars
      kanbanSidebar.querySelector('.assigned').innerHTML = '';
      kanbanSidebar.querySelector('.assigned').insertAdjacentHTML(
        'afterbegin',
        `${renderAvatar(avatars, false, 'xs', '1', el.getAttribute('data-members'))}
        <div class="avatar avatar-xs ms-1">
            <span class="avatar-initial rounded-circle bg-label-secondary">
                <i class="icon-base bx bx-plus icon-xs text-heading"></i>
            </span>
        </div>`
      );
    },

    buttonClick: (el, boardId) => {
      const addNewForm = document.createElement('form');
      addNewForm.setAttribute('class', 'new-item-form');
      addNewForm.innerHTML = `
        <div class="mb-4">
            <textarea class="form-control add-new-item" rows="2" placeholder="Add Content" autofocus required></textarea>
        </div>
        <div class="mb-4">
            <button type="submit" class="btn btn-primary btn-sm me-3">Add</button>
            <button type="button" class="btn btn-label-secondary btn-sm cancel-add-item">Cancel</button>
        </div>
      `;

      kanban.addForm(boardId, addNewForm);

      addNewForm.addEventListener('submit', e => {
        e.preventDefault();
        const currentBoard = Array.from(document.querySelectorAll(`.kanban-board[data-id="${boardId}"] .kanban-item`));
        kanban.addElement(boardId, {
          title: `<span class="kanban-text">${e.target[0].value}</span>`,
          id: `${boardId}-${currentBoard.length + 1}`
        });

        // Add dropdown to new tasks
        const kanbanTextElements = Array.from(
          document.querySelectorAll(`.kanban-board[data-id="${boardId}"] .kanban-text`)
        );
        kanbanTextElements.forEach(textElem => {
          textElem.insertAdjacentHTML('beforebegin', renderDropdown());
        });

        // Prevent sidebar from opening on dropdown click
        const newTaskDropdowns = Array.from(document.querySelectorAll('.kanban-item .kanban-tasks-item-dropdown'));
        newTaskDropdowns.forEach(dropdown => {
          dropdown.addEventListener('click', event => event.stopPropagation());
        });

        // Add delete functionality for new tasks
        const deleteTaskButtons = Array.from(
          document.querySelectorAll(`.kanban-board[data-id="${boardId}"] .delete-task`)
        );
        deleteTaskButtons.forEach(btn => {
          btn.addEventListener('click', () => {
            const taskId = btn.closest('.kanban-item').getAttribute('data-eid');
            kanban.removeElement(taskId);
          });
        });

        addNewForm.remove();
      });

      // Remove form on clicking cancel button
      addNewForm.querySelector('.cancel-add-item').addEventListener('click', () => addNewForm.remove());
    }
  });

  // Kanban Wrapper scrollbar
  if (kanbanWrapper) {
    new PerfectScrollbar(kanbanWrapper);
  }

  const kanbanContainer = document.querySelector('.kanban-container');
  const kanbanTitleBoard = Array.from(document.querySelectorAll('.kanban-title-board'));
  const kanbanItem = Array.from(document.querySelectorAll('.kanban-item'));

  // Render custom items
  if (kanbanItem.length) {
    kanbanItem.forEach(el => {
      const element = `<span class="kanban-text">${el.textContent}</span>`;
      let img = '';

      if (el.getAttribute('data-image')) {
        img = `
              <img class="img-fluid rounded mb-2"
                   src="${assetsPath}img/elements/${el.getAttribute('data-image')}">
          `;
      }

      el.textContent = '';

      if (el.getAttribute('data-badge') && el.getAttribute('data-badge-text')) {
        el.insertAdjacentHTML(
          'afterbegin',
          `${renderHeader(el.getAttribute('data-badge'), el.getAttribute('data-badge-text'))}${img}${element}`
        );
      }

      if (el.getAttribute('data-comments') || el.getAttribute('data-due-date') || el.getAttribute('data-assigned')) {
        el.insertAdjacentHTML(
          'beforeend',
          renderFooter(
            el.getAttribute('data-attachments') || 0,
            el.getAttribute('data-comments') || 0,
            el.getAttribute('data-assigned') || '',
            el.getAttribute('data-members') || ''
          )
        );
      }
    });
  }

  // Initialize tooltips for rendered items
  const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(tooltipTriggerEl => {
    new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Prevent sidebar from opening on dropdown button click
  const tasksItemDropdown = Array.from(document.querySelectorAll('.kanban-tasks-item-dropdown'));
  if (tasksItemDropdown.length) {
    tasksItemDropdown.forEach(dropdown => {
      dropdown.addEventListener('click', event => {
        event.stopPropagation();
      });
    });
  }

  // Toggle "add new" input and actions for add-new-btn
  if (kanbanAddBoardBtn) {
    kanbanAddBoardBtn.addEventListener('click', () => {
      kanbanAddNewInput.forEach(el => {
        el.value = ''; // Clear input value
        el.classList.toggle('d-none'); // Toggle visibility
      });
    });
  }

  // Render "add new" inline with boards
  if (kanbanContainer) {
    kanbanContainer.append(kanbanAddNewBoard);
  }

  // Makes kanban title editable for rendered boards
  if (kanbanTitleBoard) {
    kanbanTitleBoard.forEach(elem => {
      elem.addEventListener('mouseenter', () => {
        elem.contentEditable = 'true';
      });

      // Appends delete icon with title
      elem.insertAdjacentHTML('afterend', renderBoardDropdown());
    });
  }

  // Delete Board for rendered boards
  const deleteBoards = Array.from(document.querySelectorAll('.delete-board'));
  deleteBoards.forEach(elem => {
    elem.addEventListener('click', () => {
      const id = elem.closest('.kanban-board').getAttribute('data-id');
      kanban.removeBoard(id);
    });
  });

  // Delete task for rendered boards
  const deleteTasks = Array.from(document.querySelectorAll('.delete-task'));
  deleteTasks.forEach(task => {
    task.addEventListener('click', () => {
      const id = task.closest('.kanban-item').getAttribute('data-eid');
      kanban.removeElement(id);
    });
  });

  // Cancel "Add New Board" input
  const cancelAddNew = document.querySelector('.kanban-add-board-cancel-btn');
  if (cancelAddNew) {
    cancelAddNew.addEventListener('click', () => {
      kanbanAddNewInput.forEach(el => {
        el.classList.toggle('d-none');
      });
    });
  }

  // Add new board
  if (kanbanAddNewBoard) {
    kanbanAddNewBoard.addEventListener('submit', e => {
      e.preventDefault();
      const value = e.target.querySelector('.form-control').value.trim();
      const id = value.replace(/\s+/g, '-').toLowerCase();

      kanban.addBoards([{ id, title: value }]);

      // Add delete board option to new board and make title editable
      const newBoard = document.querySelector('.kanban-board:last-child');
      if (newBoard) {
        const header = newBoard.querySelector('.kanban-title-board');
        header.insertAdjacentHTML('afterend', renderBoardDropdown());

        // Make title editable
        header.addEventListener('mouseenter', () => {
          header.contentEditable = 'true';
        });

        // Add delete functionality to new board
        const deleteNewBoard = newBoard.querySelector('.delete-board');
        if (deleteNewBoard) {
          deleteNewBoard.addEventListener('click', () => {
            const id = deleteNewBoard.closest('.kanban-board').getAttribute('data-id');
            kanban.removeBoard(id);
          });
        }
      }

      // Hide input fields
      kanbanAddNewInput.forEach(el => {
        el.classList.add('d-none');
      });

      // Re-append the "Add New Board" form
      if (kanbanContainer) {
        kanbanContainer.append(kanbanAddNewBoard);
      }
    });
  }

  // Clear comment editor on Kanban sidebar close
  kanbanSidebar.addEventListener('hidden.bs.offcanvas', () => {
    const editor = kanbanSidebar.querySelector('.ql-editor').firstElementChild;
    if (editor) editor.innerHTML = '';
  });

  // Re-init tooltip when offcanvas opens(Bootstrap bug)
  if (kanbanSidebar) {
    kanbanSidebar.addEventListener('shown.bs.offcanvas', () => {
      const tooltipTriggerList = Array.from(kanbanSidebar.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.forEach(tooltipTriggerEl => {
        new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  }
})();
