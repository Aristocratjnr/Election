/**
 * App eCommerce review
 */

'use strict';

// apex-chart
document.addEventListener('DOMContentLoaded', function (e) {
  let cardColor, shadeColor, labelColor, headingColor, borderColor, bodyBg;

  if (isDarkStyle) {
    shadeColor = 'dark';
  } else {
    shadeColor = '';
  }
  cardColor = config.colors.cardColor;
  labelColor = config.colors.textMuted;
  headingColor = config.colors.headingColor;
  borderColor = config.colors.borderColor;
  bodyBg = config.colors.bodyBg;

  // Visitor Bar Chart
  // --------------------------------------------------------------------
  const visitorBarChartEl = document.querySelector('#reviewsChart'),
    visitorBarChartConfig = {
      chart: {
        height: 160,
        width: 190,
        type: 'bar',
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          barHeight: '75%',
          columnWidth: '40%',
          startingShape: 'rounded',
          endingShape: 'rounded',
          borderRadius: 5,
          distributed: true
        }
      },
      grid: {
        show: false,
        padding: {
          top: -25,
          bottom: -12
        }
      },
      colors: [
        config.colors_label.success,
        config.colors_label.success,
        config.colors_label.success,
        config.colors_label.success,
        config.colors.success,
        config.colors_label.success,
        config.colors_label.success
      ],
      dataLabels: {
        enabled: false
      },
      series: [
        {
          data: [20, 40, 60, 80, 100, 80, 60]
        }
      ],
      legend: {
        show: false
      },
      xaxis: {
        categories: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        },
        labels: {
          style: {
            colors: labelColor,
            fontSize: '13px'
          }
        }
      },
      yaxis: {
        labels: {
          show: false
        }
      },
      responsive: [
        {
          breakpoint: 0,
          options: {
            chart: {
              width: '100%'
            },
            plotOptions: {
              bar: {
                columnWidth: '40%'
              }
            }
          }
        },
        {
          breakpoint: 1440,
          options: {
            chart: {
              height: 150,
              width: 190,
              toolbar: {
                show: false
              }
            },
            plotOptions: {
              bar: {
                borderRadius: 6,
                columnWidth: '40%'
              }
            }
          }
        },
        {
          breakpoint: 1400,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 6,
                columnWidth: '40%'
              }
            }
          }
        },
        {
          breakpoint: 1200,
          options: {
            chart: {
              height: 130,
              width: 190,
              toolbar: {
                show: false
              }
            },
            plotOptions: {
              bar: {
                borderRadius: 6,
                columnWidth: '40%'
              }
            }
          }
        },
        {
          breakpoint: 992,
          chart: {
            height: 150,
            width: 190,
            toolbar: {
              show: false
            }
          },
          options: {
            plotOptions: {
              bar: {
                borderRadius: 5,
                columnWidth: '40%'
              }
            }
          }
        },
        {
          breakpoint: 883,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 5,
                columnWidth: '40%'
              }
            }
          }
        },
        {
          breakpoint: 768,
          options: {
            chart: {
              height: 150,
              width: 190,
              toolbar: {
                show: false
              }
            },
            plotOptions: {
              bar: {
                borderRadius: 4,
                columnWidth: '40%'
              }
            }
          }
        },
        {
          breakpoint: 576,
          options: {
            chart: {
              width: '100%',
              height: '200',
              type: 'bar'
            },
            plotOptions: {
              bar: {
                borderRadius: 6,
                columnWidth: '30% '
              }
            }
          }
        },
        {
          breakpoint: 420,
          options: {
            plotOptions: {
              chart: {
                width: '100%',
                height: '200',
                type: 'bar'
              },
              bar: {
                borderRadius: 3,
                columnWidth: '30%'
              }
            }
          }
        }
      ]
    };
  if (typeof visitorBarChartEl !== undefined && visitorBarChartEl !== null) {
    const visitorBarChart = new ApexCharts(visitorBarChartEl, visitorBarChartConfig);
    visitorBarChart.render();
  }

  // Variable declaration for table
  var dt_customer_review = document.querySelector('.datatables-review'),
    customerView = 'app-ecommerce-customer-details-overview.html',
    statusObj = {
      Pending: { title: 'Pending', class: 'bg-label-warning' },
      Published: { title: 'Published', class: 'bg-label-success' }
    };
  // reviewer datatable
  if (dt_customer_review) {
    const reviewFilter = document.createElement('div');
    reviewFilter.classList.add('review_filter');
    var dt_review = new DataTable(dt_customer_review, {
      ajax: assetsPath + 'json/app-ecommerce-reviews.json', // JSON file to add data
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'product' },
        { data: 'reviewer' },
        { data: 'review' },
        { data: 'date' },
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
          render: function (data, type, full, meta) {
            const product = full['product'];
            const companyName = full['company_name'];
            const id = full['id'];
            const image = full['product_image'];
            let output;

            if (image) {
              // For Product image
              output = `
                <img src="${assetsPath}img/ecommerce-images/${image}" alt="Product-${id}" class="rounded">
              `;
            } else {
              // For Avatar badge
              const stateNum = Math.floor(Math.random() * 6);
              const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              const state = states[stateNum];
              const initials = (product.match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase();

              output = `<span class="avatar-initial rounded bg-label-${state}">${initials}</span>`;
            }

            // Creates full output for product and company name
            const rowOutput = `
              <div class="d-flex justify-content-start align-items-center customer-name">
                <div class="avatar-wrapper">
                  <div class="avatar me-4 rounded-2 bg-label-secondary">${output}</div>
                </div>
                <div class="d-flex flex-column">
                  <span class="fw-medium text-nowrap text-heading">${product}</span>
                  <small>${companyName}</small>
                </div>
              </div>`;

            return rowOutput;
          }
        },
        {
          // Reviewer
          targets: 3,
          responsivePriority: 1,
          render: function (data, type, full, meta) {
            const reviewerName = full['reviewer'];
            const email = full['email'];
            const avatar = full['avatar'];
            let output;

            if (avatar) {
              // For Avatar image
              output = `<img src="${assetsPath}img/avatars/${avatar}" alt="Avatar" class="rounded-circle">`;
            } else {
              // For Avatar badge
              const stateNum = Math.floor(Math.random() * 6);
              const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              const state = states[stateNum];
              const initials = (reviewerName.match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase();
              output = `<span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>`;
            }

            // Creates full output for row
            const rowOutput = `
              <div class="d-flex justify-content-start align-items-center customer-name">
                <div class="avatar-wrapper">
                  <div class="avatar avatar-sm me-4">${output}</div>
                </div>
                <div class="d-flex flex-column">
                  <a href="${customerView}"><span class="fw-medium">${reviewerName}</span></a>
                  <small class="text-nowrap">${email}</small>
                </div>
              </div>`;

            return rowOutput;
          }
        },
        {
          targets: 4,
          responsivePriority: 2,
          sortable: false,
          render: function (data, type, full, meta) {
            const num = full['review'];
            const heading = full['head'];
            const comment = full['para'];

            function capitalizeFirstLetter(str) {
              if (typeof str !== 'string' || str.length === 0) {
                return str; // Return the input as it is if it's not a string or empty
              }
              return str.charAt(0).toUpperCase() + str.slice(1);
            }

            const firstCap = capitalizeFirstLetter(heading);

            // Create the rating element container
            const readOnlyRatings = document.createElement('div');
            readOnlyRatings.className = 'read-only-ratings raty mb-1';
            readOnlyRatings.setAttribute('data-number', '5');

            let r = parseInt(window.Helpers.getCssVar('gray-200', true).slice(1, 3), 16);
            let g = parseInt(window.Helpers.getCssVar('gray-200', true).slice(3, 5), 16);
            let b = parseInt(window.Helpers.getCssVar('gray-200', true).slice(5, 7), 16);
            // Initialize the Raty plugin
            if (readOnlyRatings) {
              const ratings = new Raty(readOnlyRatings, {
                score: num,
                readOnly: true, // Make the rating read-only
                starOn:
                  "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' %3E%3Cpath fill='%23FFD700' d='M21.947 9.179a1 1 0 0 0-.868-.676l-5.701-.453l-2.467-5.461a.998.998 0 0 0-1.822-.001L8.622 8.05l-5.701.453a1 1 0 0 0-.619 1.713l4.213 4.107l-1.49 6.452a1 1 0 0 0 1.53 1.057L12 18.202l5.445 3.63a1.001 1.001 0 0 0 1.517-1.106l-1.829-6.4l4.536-4.082c.297-.268.406-.686.278-1.065'/%3E%3C/svg%3E",
                starOff:
                  "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' %3E%3Cpath fill='rgb(" +
                  r +
                  ',' +
                  g +
                  ',' +
                  b +
                  ")' d='M21.947 9.179a1 1 0 0 0-.868-.676l-5.701-.453l-2.467-5.461a.998.998 0 0 0-1.822-.001L8.622 8.05l-5.701.453a1 1 0 0 0-.619 1.713l4.213 4.107l-1.49 6.452a1 1 0 0 0 1.53 1.057L12 18.202l5.445 3.63a1.001 1.001 0 0 0 1.517-1.106l-1.829-6.4l4.536-4.082c.297-.268.406-.686.278-1.065'/%3E%3C/svg%3E"
              });
              ratings.init();

              // Generate the HTML for the review
              const review = `
              <div>
                ${readOnlyRatings.outerHTML}
                <p class="h6 mb-1 text-truncate">${firstCap}</p>
                <small class="text-break">${comment}</small>
              </div>
            `;

              return review;
            }
          }
        },
        {
          // date
          targets: 5,
          render: function (data, type, full, meta) {
            var date = new Date(full.date); // convert the date string to a Date object
            var formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            return '<span class="text-nowrap">' + formattedDate + '</span>';
          }
        },
        {
          // User Status
          targets: 6,
          render: function (data, type, full, meta) {
            let status = full['status'];

            return (
              '<span class="badge ' +
              statusObj[status].class +
              '" text-capitalize>' +
              statusObj[status].title +
              '</span>'
            );
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
                <div class="dropdown">
                  <a href="javascript:void(0);" class="btn btn-icon dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">
                    <i class="icon-base bx bx-dots-vertical-rounded icon-md"></i>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end">
                    <a href="javascript:void(0);" class="dropdown-item">Download</a>
                    <a href="javascript:void(0);" class="dropdown-item">Edit</a>
                    <a href="javascript:void(0);" class="dropdown-item">Duplicate</a>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0);" class="dropdown-item delete-record text-danger">Delete</a>
                  </div>
                </div>
              </div>
            `;
          }
        }
      ],
      select: {
        style: 'multi',
        selector: 'td:nth-child(2)'
      },
      order: [[2, 'asc']],
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [
            {
              search: {
                placeholder: 'Search Review',
                text: '_INPUT_'
              }
            }
          ]
        },
        topEnd: {
          features: [
            {
              pageLength: {
                menu: [10, 25, 50, 100],
                text: '_MENU_'
              }
            },
            reviewFilter,
            {
              buttons: [
                {
                  extend: 'collection',
                  className: 'btn btn-label-primary dropdown-toggle',
                  text: '<span class="d-flex align-items-center gap-2"><i class="icon-base bx bx-export me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span></span>',
                  buttons: [
                    {
                      extend: 'print',
                      text: `<span class="d-flex align-items-center"><i class="icon-base bx bx-printer me-1"></i>Print</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              if (item.classList && item.classList.contains('user-name')) {
                                result += item.lastChild.firstChild.textContent;
                              } else {
                                result += item.textContent || item.innerText || '';
                              }
                            });
                            return result;
                          }
                        }
                      },
                      customize: function (win) {
                        win.document.body.style.color = headingColor;
                        win.document.body.style.borderColor = borderColor;
                        win.document.body.style.backgroundColor = bodyBg;
                        const table = win.document.body.querySelector('table');
                        table.classList.add('compact');
                        table.style.color = 'inherit';
                        table.style.borderColor = 'inherit';
                        table.style.backgroundColor = 'inherit';
                      }
                    },
                    {
                      extend: 'csv',
                      text: `<span class="d-flex align-items-center"><i class="icon-base bx bx-file me-1"></i>Csv</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              if (item.classList && item.classList.contains('user-name')) {
                                result += item.lastChild.firstChild.textContent;
                              } else {
                                result += item.textContent || item.innerText || '';
                              }
                            });
                            return result;
                          }
                        }
                      }
                    },
                    {
                      extend: 'excel',
                      text: `<span class="d-flex align-items-center"><i class="icon-base bx bxs-file-export me-1"></i>Excel</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              if (item.classList && item.classList.contains('user-name')) {
                                result += item.lastChild.firstChild.textContent;
                              } else {
                                result += item.textContent || item.innerText || '';
                              }
                            });
                            return result;
                          }
                        }
                      }
                    },
                    {
                      extend: 'pdf',
                      text: `<span class="d-flex align-items-center"><i class="icon-base bx bxs-file-pdf me-1"></i>Pdf</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              if (item.classList && item.classList.contains('user-name')) {
                                result += item.lastChild.firstChild.textContent;
                              } else {
                                result += item.textContent || item.innerText || '';
                              }
                            });
                            return result;
                          }
                        }
                      }
                    },
                    {
                      extend: 'copy',
                      text: `<i class="icon-base bx bx-copy me-1"></i>Copy`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [3, 4, 5, 6, 7],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              if (item.classList && item.classList.contains('user-name')) {
                                result += item.lastChild.firstChild.textContent;
                              } else {
                                result += item.textContent || item.innerText || '';
                              }
                            });
                            return result;
                          }
                        }
                      }
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
              return 'Details of ' + data['reviewer'];
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
      },
      initComplete: function () {
        this.api()
          .columns(6)
          .every(function () {
            const column = this;
            if (reviewFilter) {
              const select = document.createElement('select');
              select.className = 'form-select';
              select.innerHTML = '<option value=""> All </option>';
              reviewFilter.appendChild(select);

              select.addEventListener('change', function () {
                const val = select.value ? '^' + select.value + '$' : '';
                column.search(val, true, false).draw();
              });

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
            }
          });
      }
    });
  }

  //? The 'delete-record' class is necessary for the functionality of the following code.
  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-record')) {
      dt_review.row(e.target.closest('tr')).remove().draw();
      const modalEl = document.querySelector('.dtr-bs-modal');
      if (modalEl && modalEl.classList.contains('show')) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal?.hide();
      }
    }
  });

  // Filter form control to default size
  // ? setTimeout used for reviews table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary', classToAdd: 'btn-label-secondary' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-0' },
      { selector: '.dt-search', classToAdd: 'mb-md-6 mb-0' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm', classToAdd: 'me-md-4 me-0' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-start', classToAdd: 'px-3' },
      { selector: '.dt-layout-end', classToAdd: 'px-3' },
      { selector: '.review_filter', classToAdd: 'me-md-4' },
      { selector: '.review_filter .form-select', classToAdd: 'w-px-100' },
      { selector: '.dt-buttons', classToAdd: 'mb-0' },
      { selector: '.dt-layout-end', classToAdd: 'd-flex gap-md-0 gap-4' },
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
