/**
 * app-ecommerce-order-details Script
 */

'use strict';

// Datatable (js)

document.addEventListener('DOMContentLoaded', function (e) {
  var dt_details_table = document.querySelector('.datatables-order-details');

  // E-commerce Products datatable
  if (dt_details_table) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0');
    tableTitle.innerHTML = 'Order details';
    let tableEdit = document.createElement('h6');
    tableEdit.classList.add('m-0');
    tableEdit.innerHTML = '<a href=" javascript:void(0)">Edit</a>';
    var dt_products = new DataTable(dt_details_table, {
      ajax: assetsPath + 'json/ecommerce-order-details.json', // JSON file to add data
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'product_name' },
        { data: 'price' },
        { data: 'qty' },
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
          responsivePriority: 1,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            const name = full['product_name'];
            const productBrand = full['product_info'];
            const image = full['image'];
            let output;

            if (image) {
              // For Product image
              output = `
                <img src="${assetsPath}img/products/${image}" alt="product-${name}" class="rounded-2">
              `;
            } else {
              // For Product badge
              const stateNum = Math.floor(Math.random() * 6);
              const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              const state = states[stateNum];
              const initials = (name.match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase();

              output = `<span class="avatar-initial rounded-2 bg-label-${state}">${initials}</span>`;
            }

            // Creates full output for Product name and product info
            const rowOutput = `
              <div class="d-flex justify-content-start align-items-center text-nowrap">
                <div class="avatar-wrapper">
                  <div class="avatar me-3">${output}</div>
                </div>
                <div class="d-flex flex-column">
                  <h6 class="text-body mb-0">${name}</h6>
                  <small>${productBrand}</small>
                </div>
              </div>`;

            return rowOutput;
          }
        },
        {
          // For Price
          targets: 3,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            const price = full['price'];
            const output = '<span>$' + price + '</span>';
            return output;
          }
        },
        {
          // For Qty
          targets: 4,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            const qty = full['qty'];
            const output = '<span class="text-body">' + qty + '</span>';
            return output;
          }
        },
        {
          // Total
          targets: 5,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            const total = full['qty'] * full['price'];
            const output = '<span class="text-body">' + total + '</span>';
            return output;
          }
        }
      ],
      order: [2, ''],
      layout: {
        topStart: {
          rowClass: 'row card-header border-bottom mx-0 px-3',
          features: [tableTitle]
        },
        topEnd: {
          features: [tableEdit]
        },
        bottomStart: {
          rowClass: 'mt-0',
          features: []
        },
        bottomEnd: {}
      },
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['product_name'];
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
      }
    });
  }
  // Filter form control to default size
  // ? setTimeout used for order-details table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-start', classToAdd: 'px-3' },
      { selector: '.dt-layout-end', classToAdd: 'px-3' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' },
      { selector: '.dt-layout-full .table', classToAdd: 'mb-0' }
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

//sweet alert
(function () {
  const deleteOrder = document.querySelector('.delete-order');
  // Suspend User javascript
  if (deleteOrder) {
    deleteOrder.onclick = function () {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert order!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete order!',
        customClass: {
          confirmButton: 'btn btn-primary me-2',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {
        if (result.value) {
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Order has been removed.',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire({
            title: 'Cancelled',
            text: 'Cancelled Delete :)',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      });
    };
  }

  //for custom year
  function getCurrentYear() {
    var currentYear = new Date().getFullYear();
    return currentYear;
  }

  var year = getCurrentYear();
  document.getElementById('orderYear').innerHTML = year;
})();
