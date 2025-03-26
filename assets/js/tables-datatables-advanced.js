/**
 * DataTables Advanced (js)
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  const startDateEle = document.querySelector('.start_date');
  const endDateEle = document.querySelector('.end_date');

  // Advanced Search Functions Starts
  // --------------------------------------------------------------------

  // Datepicker for advanced filter
  const rangePickr = document.querySelector('.flatpickr-range'),
    dateFormat = 'MM/DD/YYYY';

  if (rangePickr) {
    rangePickr.flatpickr({
      mode: 'range',
      dateFormat: 'm/d/Y',
      orientation: isRtl ? 'auto right' : 'auto left',
      locale: {
        format: dateFormat
      },
      onClose: function (selectedDates, dateStr, instance) {
        let startDate = '';
        let endDate = new Date();

        if (selectedDates[0] !== undefined) {
          startDate = new Date(selectedDates[0]).toLocaleDateString('en-US', {
            month: '2-digit',
            day: '2-digit',
            year: 'numeric'
          });
          startDateEle.value = startDate; // Using `.value` to update input fields in vanilla JS
        }

        if (selectedDates[1] !== undefined) {
          endDate = new Date(selectedDates[1]).toLocaleDateString('en-US', {
            month: '2-digit',
            day: '2-digit',
            year: 'numeric'
          });
          endDateEle.value = endDate; // Using `.value` to update input fields in vanilla JS
        }

        // Trigger custom events without jQuery
        rangePickr.dispatchEvent(new Event('change'));
        rangePickr.dispatchEvent(new Event('keyup'));
      }
    });
  }

  // Advance filter function
  // We pass the column location, the start date, and the end date
  // Clear existing custom filters
  if (typeof $.fn !== 'undefined' && typeof $.fn.dataTableExt !== 'undefined') {
    $.fn.dataTableExt.afnFiltering.length = 0;
  }

  const filterByDate = function (column, startDate, endDate) {
    // Custom filter syntax requires pushing the new filter to the global filter array
    if (typeof $.fn !== 'undefined' && typeof $.fn.dataTableExt !== 'undefined') {
      $.fn.dataTableExt.afnFiltering.push(function (oSettings, aData, iDataIndex) {
        const rowDate = normalizeDate(aData[column]);
        const start = normalizeDate(startDate);
        const end = normalizeDate(endDate);

        // If our date from the row is between the start and end
        if (start <= rowDate && rowDate <= end) {
          return true;
        } else if (rowDate >= start && end === '' && start !== '') {
          return true;
        } else if (rowDate <= end && start === '' && end !== '') {
          return true;
        } else {
          return false;
        }
      });
    }
  };

  // Convert date strings to a Date object, then normalize into YYYYMMDD format
  const normalizeDate = function (dateString) {
    const date = new Date(dateString);
    const normalized =
      date.getFullYear() +
      ('0' + (date.getMonth() + 1)).slice(-2) + // Ensure month is two digits
      ('0' + date.getDate()).slice(-2); // Ensure day is two digits
    return normalized;
  };

  // Advanced Search Functions Ends

  // Ajax Sourced Server-side
  // --------------------------------------------------------------------
  const dt_ajax_table = document.querySelector('.datatables-ajax');
  if (dt_ajax_table) {
    let dt_ajax = new DataTable(dt_ajax_table, {
      processing: true,
      ajax: {
        url: assetsPath + 'json/ajax.php',
        dataSrc: 'data'
      },
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
      }
    });
  }

  // Column Search
  // --------------------------------------------------------------------

  const dt_filter_table = document.querySelector('.dt-column-search');
  if (dt_filter_table) {
    // Setup - add a text input to each footer cell
    const thead = document.querySelector('.dt-column-search thead');

    // Clone the first row and append it as the second row
    const cloneRow = thead.querySelector('tr').cloneNode(true);
    thead.appendChild(cloneRow);

    // Select the newly added second row (the cloned one)
    const secondRowCells = thead.querySelectorAll('tr:nth-child(2) th');

    secondRowCells.forEach((th, i) => {
      const title = th.textContent;
      const input = document.createElement('input');
      input.type = 'text';
      input.className = 'form-control';
      input.placeholder = `Search ${title}`;

      // Add left and right border styles to the parent element
      th.style.borderLeft = 'none';
      if (i === secondRowCells.length - 1) {
        th.style.borderRight = 'none';
      }

      th.innerHTML = '';
      th.appendChild(input);

      // Event listener for search functionality
      input.addEventListener('keyup', function () {
        if (dt_filter.column(i).search() !== this.value) {
          dt_filter.column(i).search(this.value).draw();
        }
      });

      input.addEventListener('change', function () {
        if (dt_filter.column(i).search() !== this.value) {
          dt_filter.column(i).search(this.value).draw();
        }
      });
    });

    let dt_filter = new DataTable(dt_filter_table, {
      ajax: assetsPath + 'json/table-datatable.json',
      columns: [
        { data: 'full_name' },
        { data: 'email' },
        { data: 'post' },
        { data: 'city' },
        { data: 'start_date' },
        { data: 'salary' }
      ],
      orderCellsTop: true,
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
            placeholder: 'Type search here'
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
      }
    });
  }

  // Advanced Search
  // --------------------------------------------------------------------

  const dt_adv_filter_table = document.querySelector('.dt-advanced-search');
  let dt_adv_filter;
  // Advanced Filter table
  if (dt_adv_filter_table) {
    dt_adv_filter = new DataTable(dt_adv_filter_table, {
      ajax: assetsPath + 'json/table-datatable.json',
      columns: [
        { data: '' },
        { data: 'full_name' },
        { data: 'email' },
        { data: 'post' },
        { data: 'city' },
        { data: 'start_date' },
        { data: 'salary' }
      ],
      columnDefs: [
        {
          className: 'control',
          orderable: false,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        }
      ],
      layout: {
        topStart: {
          rowClass: 'm-0',
          features: []
        },
        topEnd: {},
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: 'paging'
      },
      language: {
        paginate: {
          next: '<i class="icon-base bx bx-chevron-right scaleX-n1-rtl icon-sm"></i>',
          previous: '<i class="icon-base bx bx-chevron-left scaleX-n1-rtl icon-sm"></i>'
        }
      },
      orderCellsTop: true,
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
  }

  // on keyup from input field
  document.querySelectorAll('input.dt-input').forEach(input => {
    input.addEventListener('keyup', function () {
      const column = this.getAttribute('data-column');
      const value = this.value;
      filterColumn(column, value);
    });
  });

  // Filter column wise function
  function filterColumn(i, val) {
    if (i == 5) {
      const startDate = startDateEle.value;
      const endDate = endDateEle.value;

      if (startDate !== '' && endDate !== '') {
        // Reset custom filter
        $.fn.dataTable.ext.search.length = 0;

        // Custom date filtering logic
        filterByDate(i, startDate, endDate);
      }

      // Redraw the DataTable
      dt_adv_filter.draw();
    } else {
      // Search the column using the DataTable instance
      dt_adv_filter
        .column(i) // Access the correct column
        .search(val, false, true) // Apply the search
        .draw(); // Redraw the table
    }
  }

  // Responsive Table
  // --------------------------------------------------------------------

  const dt_responsive_table = document.querySelector('.dt-responsive');
  if (dt_responsive_table) {
    let dt_responsive = new DataTable(dt_responsive_table, {
      ajax: assetsPath + 'json/table-datatable.json',
      columns: [
        { data: 'id' },
        { data: 'full_name' },
        { data: 'email' },
        { data: 'post' },
        { data: 'city' },
        { data: 'start_date' },
        { data: 'salary' },
        { data: 'age' },
        { data: 'experience' },
        { data: 'status' }
      ],
      columnDefs: [
        {
          className: 'control',
          orderable: false,
          targets: 0,
          searchable: false,
          render: function (data, type, full, meta) {
            return '';
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
      destroy: true,
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
  }

  // Responsive with Child Rows
  // --------------------------------------------------------------------

  const dt_responsive_child_table = document.querySelector('.dt-responsive-child');
  let dt_responsive_child;
  if (dt_responsive_child_table) {
    dt_responsive_child = new DataTable(dt_responsive_child_table, {
      ajax: assetsPath + 'json/table-datatable.json',
      columns: [
        { data: null },
        { data: 'full_name' },
        { data: 'email' },
        { data: 'city' },
        { data: 'start_date' },
        { data: 'age' },
        { data: 'status' }
      ],
      columnDefs: [
        {
          className: 'dt-control',
          orderable: false,
          targets: 0,
          searchable: false,
          defaultContent: ''
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
      scrollX: true,
      language: {
        paginate: {
          next: '<i class="icon-base bx bx-chevron-right scaleX-n1-rtl icon-sm"></i>',
          previous: '<i class="icon-base bx bx-chevron-left scaleX-n1-rtl icon-sm"></i>'
        }
      }
    });
  }

  // Formatting function for row details - modify as you need
  function format(d) {
    // `d` is the original data object for the row
    return (
      '<dl>' +
      '<dt>Full name:</dt>' +
      '<dd>' +
      d.full_name +
      '</dd>' +
      '<dt>Post:</dt>' +
      '<dd>' +
      d.post +
      '</dd>' +
      '<dt>Salary:</dt>' +
      '<dd>' +
      d.salary +
      '</dd>' +
      '<dt>Experience:</dt>' +
      '<dd>' +
      d.experience +
      '</dd>' +
      '</dl>'
    );
  }

  // Add event listener for opening and closing details
  dt_responsive_child.on('click', 'td.dt-control', function (e) {
    let tr = e.target.closest('tr');
    let row = dt_responsive_child.row(tr);

    if (row.child.isShown()) {
      // This row is already open - close it
      row.child.hide();
    } else {
      // Open this row
      row.child(format(row.data())).show();
    }
  });

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-4' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-end', classToAdd: 'mt-0' },
      { selector: '.dt-layout-end .dt-search', classToAdd: 'mt-md-6 mt-0' },
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
