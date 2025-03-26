/**
 * Page Detail overview
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  const dt_customer_order = document.querySelector('.datatables-customer-order'),
    order_details = 'app-ecommerce-order-details.html',
    statusObj = {
      1: { title: 'Ready to  Pickup', class: 'bg-label-info' },
      2: { title: 'Dispatched', class: 'bg-label-warning' },
      3: { title: 'Delivered', class: 'bg-label-success' },
      4: { title: 'Out for delivery', class: 'bg-label-primary' }
    };

  // orders datatable
  if (dt_customer_order) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0');
    tableTitle.innerHTML = 'Orders placed';
    var dt_order = new DataTable(dt_customer_order, {
      ajax: assetsPath + 'json/ecommerce-customer-order.json', // JSON file to add data
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'order' },
        { data: 'date' },
        { data: 'status' },
        { data: 'spent' },
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
          // order order number
          targets: 1,
          responsivePriority: 4,
          render: function (data, type, full, meta) {
            const id = full['order'];

            return "<a href='" + order_details + "'><span>#" + id + '</span></a>';
          }
        },
        {
          // date
          targets: 2,
          render: function (data, type, full, meta) {
            const date = new Date(full.date); // convert the date string to a Date object
            const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            return '<span class="text-nowrap">' + formattedDate + '</span > ';
          }
        },
        {
          // status
          targets: 3,
          render: function (data, type, full, meta) {
            const status = full['status'];

            return (
              '<span class="badge ' +
              statusObj[status].class +
              '" text-capitalized>' +
              statusObj[status].title +
              '</span>'
            );
          }
        },
        {
          // spent
          targets: 4,
          render: function (data, type, full, meta) {
            const spent = full['spent'];

            return '<span >' + spent + '</span>';
          }
        },
        {
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return `
              <div class="text-xxl-center">
                <button class="btn btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="icon-base bx bx-dots-vertical-rounded"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end m-0">
                  <a href="javascript:void(0);" class="dropdown-item">View</a>
                  <a href="javascript:void(0);" class="dropdown-item delete-record">Delete</a>
                </div>
              </div>
            `;
          }
        }
      ],
      order: [[1, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row card-header border-bottom mx-0 px-3 py-0',
          features: [tableTitle]
        },
        topEnd: {
          search: {
            placeholder: 'Search order',
            text: '_INPUT_'
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
          next: '<i class="icon-base bx bx-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base bx bx-chevron-left scaleX-n1-rtl icon-18px"></i>'
        }
      },
      pageLength: 6,
      // For responsive popup
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Details of ' + data['order'];
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

  //? The 'delete-record' class is necessary for the functionality of the following code.
  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-record')) {
      dt_order.row(e.target.closest('tr')).remove().draw();
      const modalEl = document.querySelector('.dtr-bs-modal');
      if (modalEl && modalEl.classList.contains('show')) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal?.hide();
      }
    }
  });

  // Filter form control to default size
  // ? setTimeout used for customer-detail-overview table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-start', classToAdd: 'mb-xxl-0 mb-4' },
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

// Validation & Phone mask
