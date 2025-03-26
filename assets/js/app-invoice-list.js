/**
 * App Invoice List (js)
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const dt_invoice_table = document.querySelector('.invoice-list-table');

  if (dt_invoice_table) {
    const dt_invoice = new DataTable(dt_invoice_table, {
      ajax: assetsPath + 'json/invoice-list.json',
      columns: [
        { data: 'invoice_id' },
        { data: 'invoice_id', orderable: false, render: DataTable.render.select() },
        { data: 'invoice_id' },
        { data: 'invoice_status' },
        { data: 'issued_date' },
        { data: 'client_name' },
        { data: 'total' },
        { data: 'balance' },
        { data: 'invoice_status' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          className: 'control',
          responsivePriority: 2,
          searchable: false,
          targets: 0,
          render: function () {
            return '';
          }
        },
        {
          targets: 1,
          orderable: false,
          searchable: false,
          responsivePriority: 4,
          render: function () {
            return '<input type="checkbox" class="dt-checkboxes form-check-input">';
          }
        },
        {
          targets: 2,
          render: function (data, type, full) {
            return `<a href="app-invoice-preview.html">#${full['invoice_id']}</a>`;
          }
        },
        {
          // Invoice Status with tooltip
          targets: 3,
          render: function (data, type, full) {
            const invoiceStatus = full['invoice_status'];
            const balance = full['balance'];
            const dueDate = full['due_date'];

            const roleBadgeObj = {
              Sent: '<span class="badge p-1_5 rounded-pill bg-label-secondary"><i class="icon-base icon-16px bx bx-envelope"></i></span>',
              Draft:
                '<span class="badge p-1_5 rounded-pill bg-label-primary"><i class="icon-base icon-16px bx bx-folder"></i></span>',
              'Past Due':
                '<span class="badge p-1_5 rounded-pill bg-label-danger"><i class="icon-base icon-16px bx bx-error"></i></span>',
              'Partial Payment':
                '<span class="badge p-1_5 rounded-pill bg-label-success"><i class="icon-base icon-16px bx bx-check"></i></span>',
              Paid: '<span class="badge p-1_5 rounded-pill bg-label-warning"><i class="icon-base icon-16px bx bx-pie-chart-alt"></i></span>',
              Downloaded:
                '<span class="badge p-1_5 rounded-pill bg-label-info"><i class="icon-base icon-16px bx bx-down-arrow-alt"></i></span>'
            };

            // Sanitize tooltip content by escaping double quotes
            const tooltipContent = `
              ${invoiceStatus}<br>
              <span class="fw-medium">Balance:</span> ${balance}<br>
              <span class="fw-medium">Due Date:</span> ${dueDate}
            `.replace(/"/g, '&quot;');

            return `
              <span class="d-inline-block" data-bs-toggle="tooltip" data-bs-html="true" title="<span>${tooltipContent}">
                ${roleBadgeObj[invoiceStatus] || ''}
              </span>
              </span>
            `;
          }
        },
        {
          targets: 4,
          responsivePriority: 2,
          render: function (data, type, full) {
            const name = full['client_name'];
            const service = full['service'];
            const image = full['avatar_image'];
            const randNum = Math.floor(Math.random() * 11) + 1;
            const userImg = `${randNum}.png`;
            let output;

            if (image === true) {
              output = `<img src="${assetsPath}img/avatars/${userImg}" alt="Avatar" class="rounded-circle">`;
            } else {
              const stateNum = Math.floor(Math.random() * 6);
              const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              const state = states[stateNum];
              const initials = (name.match(/\b\w/g) || [])
                .slice(0, 2)
                .map(letter => letter.toUpperCase())
                .join('');
              output = `<span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>`;
            }

            return `
              <div class="d-flex justify-content-start align-items-center">
                <div class="avatar-wrapper">
                  <div class="avatar avatar-sm me-3">
                    ${output}
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <a href="pages-profile-user.html" class="text-heading text-truncate"><span class="fw-medium">${name}</span></a>
                  <small class="text-truncate">${service}</small>
                </div>
              </div>
            `;
          }
        },
        {
          targets: 5,
          render: function (data, type, full) {
            const total = full['total'];
            return `<span class="d-none">${total}</span>$${total}`;
          }
        },
        {
          targets: 6,
          render: function (data, type, full) {
            const dueDate = new Date(full['due_date']);
            return `
              <span class="d-none">${dueDate.toISOString().slice(0, 10).replace(/-/g, '')}</span>
              ${dueDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}
            `;
          }
        },
        {
          targets: 7,
          orderable: false,
          render: function (data, type, full) {
            const balance = full['balance'];
            if (balance === 0) {
              return '<span class="badge bg-label-success text-capitalized"> Paid </span>';
            } else {
              return `<span class="d-none">${balance}</span><span class="text-heading">${balance}</span>`;
            }
          }
        },
        {
          targets: 8,
          visible: false
        },
        {
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function () {
            return (
              '<div class="d-flex align-items-center">' +
              '<a href="javascript:;" data-bs-toggle="tooltip" class="btn btn-icon delete-record" data-bs-placement="top" title="Delete"><i class="icon-base bx bx-trash icon-md"></i></a>' +
              '<a href="app-invoice-preview.html" data-bs-toggle="tooltip" class="btn btn-icon" data-bs-placement="top" title="Preview Invoice"><i class="icon-base bx bx-show icon-md"></i></a>' +
              '<div class="dropdown">' +
              '<a href="javascript:;" class="btn dropdown-toggle hide-arrow btn-icon p-0" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded icon-md"></i></a>' +
              '<div class="dropdown-menu dropdown-menu-end">' +
              '<a href="javascript:;" class="dropdown-item">Download</a>' +
              '<a href="app-invoice-edit.html" class="dropdown-item">Edit</a>' +
              '<a href="javascript:;" class="dropdown-item">Duplicate</a>' +
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
      displayLength: 10,
      layout: {
        topStart: {
          rowClass: 'row m-3 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [10, 25, 50, 100],
                text: 'Show_MENU_'
              },
              buttons: [
                {
                  text: '<i class="icon-base icon-16px bx bx-plus me-md-2"></i><span class="d-md-inline-block d-none">Create Invoice</span>',
                  className: 'btn btn-primary',
                  action: function () {
                    window.location = 'app-invoice-add.html';
                  }
                }
              ]
            }
          ]
        },
        topEnd: {
          rowClass: 'row mx-3 justify-content-between',
          features: [
            {
              search: {
                placeholder: 'Search Invoice',
                text: '_INPUT_'
              }
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
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Details of ' + data['client_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
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
      },
      initComplete: function () {
        // Ensure the container for the Invoice Status filter is created
        let invoiceStatusContainer = document.querySelector('.invoice_status');
        if (!invoiceStatusContainer) {
          // Create the container if it doesn't exist
          invoiceStatusContainer = document.createElement('div');
          invoiceStatusContainer.className = 'invoice_status';

          // Append it to a suitable location in your DataTable's layout
          // Example: Appending to the filter area (adjust as needed)
          const filterArea = document.querySelector('.dt-layout-end');
          if (filterArea) {
            filterArea.appendChild(invoiceStatusContainer);
          }
        }

        // Adding role filter once the table is initialized
        this.api()
          .columns(8)
          .every(function () {
            const column = this;

            // Create the dropdown for "Invoice Status"
            const select = document.createElement('select');
            select.id = 'UserRole';
            select.className = 'form-select';
            select.innerHTML = '<option value=""> Invoice Status </option>';

            // Append the dropdown to the invoice status container
            invoiceStatusContainer.appendChild(select);

            // Add change event listener to filter the column based on selected value
            select.addEventListener('change', function () {
              const val = select.value ? `^${select.value}$` : '';
              column.search(val, true, false).draw();
            });

            // Populate the dropdown with unique values from the column data
            column
              .data()
              .unique()
              .sort()
              .each(function (d) {
                const option = document.createElement('option');
                option.value = d;
                option.className = 'text-capitalize';
                option.textContent = d;
                select.appendChild(option);
              });
          });
      }
    });

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
      const invoiceTable = document.querySelector('.invoice-list-table');
      const modal = document.querySelector('.dtr-bs-modal');

      if (invoiceTable && invoiceTable.classList.contains('collapsed')) {
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
        const tableBody = invoiceTable?.querySelector('tbody');
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

    // Initialize tooltips on each table draw
    dt_invoice.on('draw', function () {
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      tooltipTriggerList.forEach(tooltipTriggerEl => {
        new bootstrap.Tooltip(tooltipTriggerEl, {
          boundary: document.body
        });
      });
    });
  }

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-buttons', classToAdd: 'mb-0' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-length', classToAdd: 'me-2 mb-6' },
      { selector: '.dt-search', classToAdd: 'me-sm-0 me-4' },
      {
        selector: '.dt-layout-end',
        classToRemove: 'justify-content-between',
        classToAdd:
          'justify-content-md-between justify-content-center d-flex flex-wrap gap-sm-4 mb-sm-0 mb-6 mt-0 pe-md-3 ps-0'
      },
      { selector: '.dt-layout-start', classToAdd: 'px-3 pe-md-0 mt-0' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
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
