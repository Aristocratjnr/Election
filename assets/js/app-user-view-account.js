/**
 * App User View - Account (js)
 */
'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  // Variable declaration for table
  const dt_project_table = document.querySelector('.datatable-project'),
    dt_invoice_table = document.querySelector('.datatable-invoice');

  // Project datatable
  // --------------------------------------------------------------------
  if (dt_project_table) {
    var dt_project = new DataTable(dt_project_table, {
      ajax: assetsPath + 'json/user-profile.json', // JSON file to add data
      columns: [
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'project_name' },
        { data: 'project_leader' },
        { data: 'id' },
        { data: 'status' },
        { data: 'id' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          searchable: false,
          responsivePriority: 3,
          checkboxes: true,
          render: function () {
            return '<input type="checkbox" class="dt-checkboxes form-check-input">';
          },
          checkboxes: {
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          }
        },
        {
          targets: 2,
          responsivePriority: 4,
          render: function (data, type, full, meta) {
            var userImg = full['project_img'],
              name = full['project_name'],
              date = full['date'];
            var output;

            if (userImg) {
              // For Avatar image
              output =
                '<img src="' + assetsPath + 'img/icons/brands/' + userImg + '" alt="Avatar" class="rounded-circle">';
            } else {
              // For Avatar badge
              var stateNum = Math.floor(Math.random() * 6);
              var states = ['success', 'danger', 'warning', 'info', 'primary', 'secondary'];
              var state = states[stateNum],
                initials = name.match(/\b\w/g) || [];
              initials = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
              output = '<span class="avatar-initial rounded-circle bg-label-' + state + '">' + initials + '</span>';
            }
            // Creates full output for row
            var rowOutput =
              '<div class="d-flex justify-content-left align-items-center">' +
              '<div class="avatar-wrapper">' +
              '<div class="avatar avatar-sm me-3">' +
              output +
              '</div>' +
              '</div>' +
              '<div class="d-flex flex-column gap-50">' +
              '<span class="text-truncate fw-medium text-heading">' +
              name +
              '</span>' +
              '<small class="text-truncate">' +
              date +
              '</small>' +
              '</div>' +
              '</div>';

            return rowOutput;
          }
        },
        {
          // Task
          targets: 3,
          render: function (data, type, full, meta) {
            var task = full['project_leader'];
            return '<span class="text-heading">' + task + '</span>';
          }
        },
        {
          targets: 4,
          orderable: false,
          searchable: false,
          render: function (data, type, full, meta) {
            const team = full['team'];
            let teamItem = '';
            let teamCount = 0;

            // Iterate through team members and generate the list items
            for (let i = 0; i < team.length; i++) {
              teamItem += `
                <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="Kim Karlos" class="avatar avatar-xs pull-up">
                  <img class="rounded-circle" src="${assetsPath}img/avatars/${team[i]}" alt="Avatar">
                </li>
              `;
              teamCount++;
              if (teamCount > 2) break;
            }

            // Check if there are more than 2 team members, and add the remaining avatars
            if (teamCount > 2) {
              const remainingAvatars = team.length - 3;
              if (remainingAvatars > 0) {
                teamItem += `
                  <li class="avatar avatar-xs">
                    <span class="avatar-initial rounded-circle pull-up" data-bs-toggle="tooltip" data-bs-placement="top" title="${remainingAvatars} more">+${remainingAvatars}</span>
                  </li>
                `;
              }
            }

            // Combine the team items into the final output
            const teamOutput = `
              <div class="d-flex align-items-center">
                <ul class="list-unstyled d-flex align-items-center avatar-group mb-0 z-2">
                  ${teamItem}
                </ul>
              </div>
            `;

            return teamOutput;
          }
        },
        {
          targets: -2,
          render: function (data, type, full, meta) {
            const statusNumber = full['status'];
            return `
              <div class="d-flex align-items-center">
                <div class="progress w-100 me-3" style="height: 6px;">
                  <div class="progress-bar" style="width: ${statusNumber}" aria-valuenow="${statusNumber}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <span class="text-heading">${statusNumber}</span>
              </div>
            `;
          }
        },
        {
          // Actions
          targets: -1,
          searchable: false,
          title: 'Action',
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-block">' +
              '<a href="javascript:;" class="btn btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded icon-md"></i></a>' +
              '<div class="dropdown-menu dropdown-menu-end m-0">' +
              '<a href="javascript:;" class="dropdown-item">Details</a>' +
              '<a href="javascript:;" class="dropdown-item">Archive</a>' +
              '<div class="dropdown-divider"></div>' +
              '<a href="javascript:;" class="dropdown-item text-danger delete-record">Delete</a>' +
              '</div>' +
              '</div>'
            );
          }
        }
      ],
      select: {
        style: 'multi',
        selector: 'td:nth-child(2)'
      },
      order: [[2, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row mx-md-2 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [7, 10, 25, 50, 75, 100]
              }
            }
          ]
        },
        topEnd: {
          search: {
            placeholder: 'Search Project',
            text: '_INPUT_'
          }
        },
        bottomStart: {
          rowClass: 'row mx-2 justify-content-between',
          features: ['info']
        },
        bottomEnd: {
          paging: {
            firstLast: false
          }
        }
      },
      displayLength: 7,
      language: {
        lengthMenu: '_MENU_',
        paginate: {
          next: '<i class="icon-base bx bx-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base bx bx-chevron-left scaleX-n1-rtl icon-18px"></i>'
        }
      },
      // For responsive popup
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Details of ' + data['project_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== '' // Do not show row in modal popup if title is blank (for check box)
                  ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                      <td>${col.title}:</td>
                      <td>${col.data}</td>
                    </tr>`
                  : '';
              })
              .join('');

            if (data) {
              const div = document.createElement('div');
              div.classList.add('table-responsive');
              const table = document.createElement('table');
              div.appendChild(table);
              table.classList.add('table');
              const tbody = document.createElement('tbody');
              tbody.innerHTML = data;
              table.appendChild(tbody);
              return div;
            }
            return false;
          }
        }
      }
    });
    //? The 'delete-record' class is necessary for the functionality of the following code.
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('delete-record')) {
        dt_project.row(e.target.closest('tr')).remove().draw();
        const modalEl = document.querySelector('.dtr-bs-modal');
        if (modalEl && modalEl.classList.contains('show')) {
          const modal = bootstrap.Modal.getInstance(modalEl);
          modal?.hide();
        }
      }
    });
  }

  // Invoice datatable
  // --------------------------------------------------------------------
  if (dt_invoice_table) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center', 'pt-6', 'pt-md-0');
    tableTitle.innerHTML = 'Invoice List';
    const dt_invoice = new DataTable(dt_invoice_table, {
      ajax: assetsPath + 'json/invoice-list.json', // JSON file to add data
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'invoice_id' },
        { data: 'invoice_status' },
        { data: 'total' },
        { data: 'issued_date' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          orderable: false,
          searchable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // Invoice ID
          targets: 1,
          render: (data, type, full, meta) => {
            const invoiceId = full['invoice_id'];
            // Creates full output for row
            const rowOutput = `<a href="app-invoice-preview.html"><span>#${invoiceId}</span></a>`;
            return rowOutput;
          }
        },
        {
          // Invoice status
          targets: 2,
          render: (data, type, full, meta) => {
            const invoiceStatus = full['invoice_status'];
            const dueDate = full['due_date'];
            const balance = full['balance'];

            const roleBadgeObj = {
              Sent: `<span class="badge badge-center d-flex align-items-center justify-content-center rounded-pill bg-label-success w-px-30 h-px-30"><i class="bx bx-check icon-xs"></i></span>`,
              Draft: `<span class="badge badge-center d-flex align-items-center justify-content-center rounded-pill bg-label-primary w-px-30 h-px-30"><i class="bx bx-folder icon-xs"></i></span>`,
              'Past Due': `<span class="badge badge-center d-flex align-items-center justify-content-center rounded-pill bg-label-danger w-px-30 h-px-30"><i class="bx bx-error icon-xs"></i></span>`,
              'Partial Payment': `<span class="badge badge-center d-flex align-items-center justify-content-center rounded-pill bg-label-secondary w-px-30 h-px-30"><i class="bx bx-envelope icon-xs"></i></span>`,
              Paid: `<span class="badge badge-center d-flex align-items-center justify-content-center rounded-pill bg-label-warning w-px-30 h-px-30"><i class="bx bx-pie-chart-alt icon-xs"></i></span>`,
              Downloaded: `<span class="badge badge-center d-flex align-items-center justify-content-center rounded-pill bg-label-info w-px-30 h-px-30"><i class="bx bx-down-arrow-alt icon-xs"></i></span>`
            };

            return `
              <span class='d-inline-block' data-bs-toggle='tooltip' data-bs-html='true'
                    title='<span>${invoiceStatus}<br>
                           <span class="fw-medium">Balance:</span> ${balance}<br>
                           <span class="fw-medium">Due Date:</span> ${dueDate}</span>'>
                ${roleBadgeObj[invoiceStatus]}
              </span>
            `;
          }
        },
        {
          // Total Invoice Amount
          targets: 3,
          render: function (data, type, full, meta) {
            const total = full['total'];
            return '$' + total;
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          orderable: false,
          render: (data, type, full, meta) => {
            return `
              <div class="d-flex align-items-center">
                <a href="javascript:;" class="btn btn-icon delete-record"><i class="bx bx-trash icon-md"></i></a>
                <a href="app-invoice-preview.html" class="btn btn-icon" data-bs-toggle="tooltip" title="Preview">
                  <i class="bx bx-show icon-md"></i>
                </a>
                <div class="d-inline-block">
                  <a href="javascript:;" class="btn btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded icon-md"></i>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end m-0">
                    <a href="javascript:;" class="dropdown-item">Download</a>
                    <a href="app-invoice-edit.html" class="dropdown-item">Edit</a>
                    <a href="javascript:;" class="dropdown-item">Duplicate</a>
                  </div>
                </div>
              </div>
            `;
          }
        }
      ],
      order: [[1, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row border-bottom mx-0 px-3',
          features: [tableTitle]
        },
        topEnd: {
          features: [
            {
              pageLength: {
                menu: [10, 25, 50, 100],
                text: '_MENU_'
              }
            },
            {
              buttons: [
                {
                  extend: 'collection',
                  className: 'btn btn-label-secondary dropdown-toggle float-sm-end mb-3 mb-md-0 mt-md-0 mt-5',
                  text: '<i class="icon-base bx bx-export icon-sm me-2"></i>Export',
                  buttons: [
                    {
                      extend: 'print',
                      text: '<i class="icon-base bx bx-printer me-2"></i>Print',
                      className: 'dropdown-item',
                      exportOptions: { columns: [1, 2, 3, 4] }
                    },
                    {
                      extend: 'csv',
                      text: '<i class="icon-base bx bx-file me-2"></i>Csv',
                      className: 'dropdown-item',
                      exportOptions: { columns: [1, 2, 3, 4] }
                    },
                    {
                      extend: 'excel',
                      text: '<i class="icon-base bx bxs-file-export me-2"></i>Excel',
                      className: 'dropdown-item',
                      exportOptions: { columns: [1, 2, 3, 4] }
                    },
                    {
                      extend: 'pdf',
                      text: '<i class="icon-base bx bxs-file-pdf me-2"></i>Pdf',
                      className: 'dropdown-item',
                      exportOptions: { columns: [1, 2, 3, 4] }
                    },
                    {
                      extend: 'copy',
                      text: '<i class="icon-base bx bx-copy me-2"></i>Copy',
                      className: 'dropdown-item',
                      exportOptions: { columns: [1, 2, 3, 4] }
                    }
                  ]
                }
              ]
            }
          ]
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: {
          paging: {
            firstLast: false
          }
        }
      },
      language: {
        paginate: {
          next: '<i class="icon-base bx bx-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base bx bx-chevron-left scaleX-n1-rtl icon-18px"></i>'
        }
      },
      // For responsive popup
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Details of ' + data['invoice_id'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== '' // Do not show row in modal popup if title is blank (for check box)
                  ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                      <td>${col.title}:</td>
                      <td>${col.data}</td>
                    </tr>`
                  : '';
              })
              .join('');

            if (data) {
              const div = document.createElement('div');
              div.classList.add('table-responsive');
              const table = document.createElement('table');
              div.appendChild(table);
              table.classList.add('table');
              const tbody = document.createElement('tbody');
              tbody.innerHTML = data;
              table.appendChild(tbody);
              return div;
            }
            return false;
          }
        }
      }
    });

    //? The 'delete-record' class is necessary for the functionality of the following code.
    function deleteRecord(event) {
      let row = document.querySelector('.dtr-expanded');
      if (event) {
        row = event.target.parentElement.closest('tr');
      }
      if (row) {
        dt_invoice.row(row).remove().draw();
      }
    }

    function bindDeleteEvent() {
      const dt_invoice_table = document.querySelector('.datatable-invoice');
      const modal = document.querySelector('.dtr-bs-modal');

      if (dt_invoice_table && dt_invoice_table.classList.contains('collapsed')) {
        if (modal) {
          modal.addEventListener('click', function (event) {
            if (event.target.parentElement.classList.contains('delete-record')) {
              deleteRecord();
              const closeButton = modal.querySelector('.btn-close');
              if (closeButton) closeButton.click(); // Simulates a click on the close button
            }
          });
        }
      } else {
        const tableBody = dt_invoice_table?.querySelector('tbody');
        if (tableBody) {
          tableBody.addEventListener('click', function (event) {
            if (event.target.parentElement.classList.contains('delete-record')) {
              deleteRecord(event);
            }
          });
        }
      }
    }

    // Initial event binding
    bindDeleteEvent();

    // Re-bind events when modal is shown or hidden
    document.addEventListener('show.bs.modal', function (event) {
      if (event.target.classList.contains('dtr-bs-modal')) {
        bindDeleteEvent();
      }
    });

    document.addEventListener('hide.bs.modal', function (event) {
      if (event.target.classList.contains('dtr-bs-modal')) {
        bindDeleteEvent();
      }
    });

    // On each datatable draw, initialize tooltip
    dt_invoice.on('draw.dt', function () {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
          boundary: document.body
        });
      });
    });
  }

  // Filter form control to default size
  // ? setTimeout used for project-list and invoice-list table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm', classToAdd: 'ms-0' },
      { selector: '.dt-length', classToAdd: 'mb-md-6 mb-0' },
      { selector: '.dt-buttons', classToAdd: 'justify-content-center' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-start', classToAdd: 'px-4 mt-0' },
      { selector: '.dt-layout-end', classToAdd: 'px-4 mt-0 gap-2' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
    ];

    // Delete record
    elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
      document.querySelectorAll(selector).forEach(element => {
        if (classToRemove) {
          classToRemove.split(' ').forEach(className => element.classList.remove(className));
        }
        if (classToAdd) {
          classToAdd.split(' ').forEach(className => element.classList.add(className));
        }
      });
    });
  }, 100);
});
