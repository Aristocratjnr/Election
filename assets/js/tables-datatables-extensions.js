/**
 * DataTables Extensions (js)
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  const dt_scrollable_table = document.querySelector('.dt-scrollableTable');
  let dt_scrollableTable;

  // Scrollable
  // --------------------------------------------------------------------

  if (dt_scrollable_table) {
    dt_scrollableTable = new DataTable(dt_scrollable_table, {
      ajax: assetsPath + 'json/table-datatable.json',
      columns: [
        { data: 'full_name' },
        { data: 'post' },
        { data: 'email' },
        { data: 'city' },
        { data: 'start_date' },
        { data: 'salary' },
        { data: 'age' },
        { data: 'experience' },
        { data: '' },
        { data: '' }
      ],
      columnDefs: [
        {
          // Label
          targets: -2,
          render: function (data, type, full, meta) {
            const statusNumber = full.status;
            const statuses = {
              1: { title: 'Current', class: 'bg-label-primary' },
              2: { title: 'Professional', class: 'bg-label-success' },
              3: { title: 'Rejected', class: 'bg-label-danger' },
              4: { title: 'Resigned', class: 'bg-label-warning' },
              5: { title: 'Applied', class: 'bg-label-info' }
            };

            if (typeof statuses[statusNumber] === 'undefined') {
              return data;
            }

            return `
              <span class="badge ${statuses[statusNumber].class}">
                ${statuses[statusNumber].title}
              </span>
            `;
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          searchable: false,
          className: 'd-flex align-items-center',
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-block">' +
              '<a href="javascript:;" class="btn btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded icon-base"></i></a>' +
              '<div class="dropdown-menu dropdown-menu-end m-0">' +
              '<a href="javascript:;" class="dropdown-item">Details</a>' +
              '<a href="javascript:;" class="dropdown-item">Archive</a>' +
              '<div class="dropdown-divider"></div>' +
              '<a href="javascript:;" class="dropdown-item text-danger delete-record">Delete</a>' +
              '</div>' +
              '</div>' +
              '<a href="javascript:;" class="item-edit text-body"><i class="bx bxs-edit icon-base"></i></a>'
            );
          }
        }
      ],
      // Scroll options
      scrollY: '300px',
      scrollX: true,
      layout: {
        topStart: {
          rowClass: 'row mx-3 my-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [7, 10, 25, 50, 100],
                text: 'Show_MENU_entries'
              }
            }
          ]
        },
        topEnd: {
          search: {
            placeholder: ''
          }
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
          next: '<i class="icon-base bx bx-chevron-right scaleX-n1-rtl icon-sm"></i>',
          previous: '<i class="icon-base bx bx-chevron-left scaleX-n1-rtl icon-sm"></i>'
        }
      },
      initComplete: function (settings, json) {
        // Add the mti-n1 class to the first row in tbody
        dt_scrollable_table.querySelector('tbody tr:first-child').classList.add('border-top-0');
      }
    });
  }

  // FixedHeader
  // --------------------------------------------------------------------

  const dt_fixedheader_table = document.querySelector('.dt-fixedheader');
  let dt_fixedheader;

  if (dt_fixedheader_table) {
    dt_fixedheader = new DataTable(dt_fixedheader_table, {
      ajax: assetsPath + 'json/table-datatable.json',
      columns: [
        { data: '' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'id' },
        { data: 'full_name' },
        { data: 'email' },
        { data: 'start_date' },
        { data: 'salary' },
        { data: 'status' },
        { data: '' }
      ],
      columnDefs: [
        {
          className: 'control',
          orderable: false,
          targets: 0,
          responsivePriority: 3,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          render: function () {
            return '<input type="checkbox" class="dt-checkboxes form-check-input">';
          },
          checkboxes: {
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          },
          responsivePriority: 4
        },
        {
          targets: 2,
          visible: false
        },
        {
          // Avatar image/badge, Name and post
          targets: 3,
          render: function (data, type, full, meta) {
            const userImg = full.avatar;
            const name = full.full_name;
            const post = full.post;
            let output;

            if (userImg) {
              // For Avatar image
              output = `<img src="${assetsPath}img/avatars/${userImg}" alt="Avatar" class="rounded-circle">`;
            } else {
              // For Avatar badge
              const stateNum = Math.floor(Math.random() * 6);
              const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              const state = states[stateNum];
              const initials = (name.match(/\b\w/g) || []).map(i => i.toUpperCase()).join('');
              output = `<span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>`;
            }

            // Creates full output for row
            const rowOutput = `
              <div class="d-flex justify-content-start align-items-center">
                <div class="avatar-wrapper">
                  <div class="avatar me-2">
                    ${output}
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <span class="emp_name text-truncate">${name}</span>
                  <small class="emp_post text-truncate text-body-secondary">${post}</small>
                </div>
              </div>
            `;

            return rowOutput;
          },
          responsivePriority: 5
        },
        {
          responsivePriority: 1,
          targets: 4
        },
        {
          responsivePriority: 2,
          targets: 6
        },

        {
          // Label
          targets: -2,
          render: function (data, type, full, meta) {
            const statusNumber = full.status;
            const statuses = {
              1: { title: 'Current', class: 'bg-label-primary' },
              2: { title: 'Professional', class: 'bg-label-success' },
              3: { title: 'Rejected', class: 'bg-label-danger' },
              4: { title: 'Resigned', class: 'bg-label-warning' },
              5: { title: 'Applied', class: 'bg-label-info' }
            };

            if (typeof statuses[statusNumber] === 'undefined') {
              return data;
            }

            return `
              <span class="badge ${statuses[statusNumber].class}">
                ${statuses[statusNumber].title}
              </span>
            `;
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          className: 'd-flex align-items-center',
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-block">' +
              '<a href="javascript:;" class="btn btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded icon-base"></i></a>' +
              '<div class="dropdown-menu dropdown-menu-end m-0">' +
              '<a href="javascript:;" class="dropdown-item">Details</a>' +
              '<a href="javascript:;" class="dropdown-item">Archive</a>' +
              '<div class="dropdown-divider"></div>' +
              '<a href="javascript:;" class="dropdown-item text-danger delete-record">Delete</a>' +
              '</div>' +
              '</div>' +
              '<a href="javascript:;" class="btn btn-icon item-edit"><i class="bx bxs-edit icon-base"></i></a>'
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
          rowClass: 'row mx-3 my-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [7, 10, 25, 50, 100],
                text: 'Show_MENU_entries'
              }
            }
          ]
        },
        topEnd: {
          search: {
            placeholder: ''
          }
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
      displayLength: 7,
      language: {
        paginate: {
          next: '<i class="icon-base bx bx-chevron-right scaleX-n1-rtl icon-sm"></i>',
          previous: '<i class="icon-base bx bx-chevron-left scaleX-n1-rtl icon-sm"></i>'
        }
      },
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['full_name'];
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
    // Fixed header
    if (window.Helpers.isNavbarFixed()) {
      const navHeight = document.getElementById('layout-navbar').offsetHeight;
      new DataTable.FixedHeader(dt_fixedheader).headerOffset(navHeight);
    } else {
      new DataTable.FixedHeader(dt_fixedheader);
    }

    //? The 'delete-record' class is necessary for the functionality of the following code.
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('delete-record')) {
        dt_fixedheader.row(e.target.closest('tr')).remove().draw();
        const modalEl = document.querySelector('.dtr-bs-modal');
        if (modalEl && modalEl.classList.contains('show')) {
          const modal = bootstrap.Modal.getInstance(modalEl);
          modal?.hide();
        }
      }
    });
  }

  // FixedColumns
  // --------------------------------------------------------------------

  const dt_fixedcolumns_table = document.querySelector('.dt-fixedcolumns');
  let dt_fixedcolumns;

  if (dt_fixedcolumns_table) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center', 'pb-md-0', 'pb-6');
    tableTitle.innerHTML = 'Fixed Columns';
    dt_fixedcolumns = new DataTable(dt_fixedcolumns_table, {
      ajax: assetsPath + 'json/table-datatable.json',
      columns: [
        { data: 'full_name' },
        { data: 'post' },
        { data: 'email' },
        { data: 'city' },
        { data: 'start_date' },
        { data: 'salary' },
        { data: 'age' },
        { data: 'experience' },
        { data: 'status' },
        { data: 'id' }
      ],
      columnDefs: [
        {
          // Label
          targets: -2,
          render: function (data, type, full, meta) {
            const statusNumber = full.status;
            const statuses = {
              1: { title: 'Current', class: 'bg-label-primary' },
              2: { title: 'Professional', class: 'bg-label-success' },
              3: { title: 'Rejected', class: 'bg-label-danger' },
              4: { title: 'Resigned', class: 'bg-label-warning' },
              5: { title: 'Applied', class: 'bg-label-info' }
            };

            if (typeof statuses[statusNumber] === 'undefined') {
              return data;
            }

            return `
              <span class="badge ${statuses[statusNumber].class}">
                ${statuses[statusNumber].title}
              </span>
            `;
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          searchable: false,
          className: 'd-flex align-items-center',
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-block">' +
              '<a href="javascript:;" class="btn btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded icon-base"></i></a>' +
              '<div class="dropdown-menu dropdown-menu-end m-0">' +
              '<a href="javascript:;" class="dropdown-item">Details</a>' +
              '<a href="javascript:;" class="dropdown-item">Archive</a>' +
              '<div class="dropdown-divider"></div>' +
              '<a href="javascript:;" class="dropdown-item text-danger delete-record"></i>Delete</a>' +
              '</div>' +
              '</div>' +
              '<a href="javascript:;" class="item-edit text-body"><i class="bx bxs-edit icon-base"></i></a>'
            );
          }
        }
      ],
      layout: {
        topStart: {
          rowClass: 'row card-header pt-0 pb-0',
          features: [tableTitle]
        },
        topEnd: {
          search: {
            placeholder: ''
          }
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
      scrollY: 300,
      scrollX: true,
      scrollCollapse: true,
      paging: false,
      info: false,
      // Fixed column option
      fixedColumns: {
        start: 1
      },
      initComplete: function (settings, json) {
        // Add the mti-n1 class to the first row in tbody
        dt_fixedcolumns_table.querySelector('tbody tr:first-child').classList.add('border-top-0');
      }
    });

    //? The 'delete-record' class is necessary for the functionality of the following code.
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('delete-record')) {
        dt_fixedcolumns.row(e.target.closest('tr')).remove().draw();
        const modalEl = document.querySelector('.dtr-bs-modal');
        if (modalEl && modalEl.classList.contains('show')) {
          const modal = bootstrap.Modal.getInstance(modalEl);
          modal?.hide();
        }
      }
    });
  }

  // Select
  // --------------------------------------------------------------------

  const dt_select_table = document.querySelector('.dt-select-table');
  let dt_select;

  if (dt_select_table) {
    dt_select = new DataTable(dt_select_table, {
      ajax: assetsPath + 'json/table-datatable.json',
      columns: [
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'full_name' },
        { data: 'post' },
        { data: 'email' },
        { data: 'city' },
        { data: 'start_date' },
        { data: 'salary' },
        { data: 'status' }
      ],
      columnDefs: [
        {
          // For Checkboxes
          targets: 0,
          searchable: false,
          orderable: false,
          render: function () {
            return '<input type="checkbox" class="dt-checkboxes form-check-input">';
          },
          checkboxes: {
            selectRow: true,
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          }
        },
        {
          // Label
          targets: -1,
          render: function (data, type, full, meta) {
            const statusNumber = full.status;
            const statuses = {
              1: { title: 'Current', class: 'bg-label-primary' },
              2: { title: 'Professional', class: 'bg-label-success' },
              3: { title: 'Rejected', class: 'bg-label-danger' },
              4: { title: 'Resigned', class: 'bg-label-warning' },
              5: { title: 'Applied', class: 'bg-label-info' }
            };

            if (typeof statuses[statusNumber] === 'undefined') {
              return data;
            }

            return `
              <span class="badge ${statuses[statusNumber].class}">
                ${statuses[statusNumber].title}
              </span>
            `;
          }
        }
      ],
      order: [[1, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row mx-3 my-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [7, 10, 25, 50, 100],
                text: 'Show_MENU_entries'
              }
            }
          ]
        },
        topEnd: {
          search: {
            placeholder: ''
          }
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
          next: '<i class="icon-base bx bx-chevron-right scaleX-n1-rtl icon-sm"></i>',
          previous: '<i class="icon-base bx bx-chevron-left scaleX-n1-rtl icon-sm"></i>'
        }
      },
      select: {
        // Select style
        style: 'multi'
      }
    });
  }

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-4' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-end', classToAdd: 'mt-0' },
      { selector: '.dt-layout-end .dt-search', classToAdd: 'mt-0 mt-md-6' }
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
