/**
 *  Logistics Dashboard
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let labelColor, headingColor, borderColor, legendColor, fontFamily;

  labelColor = config.colors.textMuted;
  headingColor = config.colors.headingColor;
  borderColor = config.colors.borderColor;
  legendColor = config.colors.bodyColor;
  fontFamily = config.fontFamily;

  // Chart Colors
  const chartColors = {
    donut: {
      series1: config.colors.success,
      series2: 'color-mix(in sRGB, ' + config.colors.success + ' 80%, ' + config.colors.cardColor + ')',
      series3: 'color-mix(in sRGB, ' + config.colors.success + ' 60%, ' + config.colors.cardColor + ')',
      series4: 'color-mix(in sRGB, ' + config.colors.success + ' 40%, ' + config.colors.cardColor + ')'
    },
    line: {
      series1: config.colors.warning,
      series2: config.colors.primary,
      series3: '#7367f029'
    }
  };

  // Shipment statistics Chart
  // --------------------------------------------------------------------
  const shipmentEl = document.querySelector('#shipmentStatisticsChart'),
    shipmentConfig = {
      series: [
        {
          name: 'Shipment',
          type: 'column',
          data: [38, 45, 33, 38, 32, 50, 48, 40, 42, 37]
        },
        {
          name: 'Delivery',
          type: 'line',
          data: [23, 28, 23, 32, 28, 44, 32, 38, 26, 34]
        }
      ],
      chart: {
        height: 320,
        type: 'line',
        stacked: false,
        parentHeightOffset: 0,
        toolbar: { show: false },
        zoom: { enabled: false }
      },
      markers: {
        size: 5,
        colors: [config.colors.white],
        strokeColors: chartColors.line.series2,
        hover: { size: 6 },
        borderRadius: 4
      },
      stroke: {
        curve: 'smooth',
        width: [0, 3],
        lineCap: 'round'
      },
      legend: {
        show: true,
        position: 'bottom',
        markers: {
          size: 4,
          offsetX: -3,
          strokeWidth: 0
        },
        height: 40,
        itemMargin: {
          horizontal: 10,
          vertical: 0
        },
        fontSize: '15px',
        fontFamily: fontFamily,
        fontWeight: 400,
        labels: {
          colors: headingColor,
          useSeriesColors: false
        },
        offsetY: 0
      },
      grid: {
        strokeDashArray: 8,
        borderColor
      },
      colors: [chartColors.line.series1, chartColors.line.series2],
      fill: {
        opacity: [1, 1]
      },
      plotOptions: {
        bar: {
          columnWidth: '30%',
          startingShape: 'rounded',
          endingShape: 'rounded',
          borderRadius: 4
        }
      },
      dataLabels: { enabled: false },
      xaxis: {
        tickAmount: 10,
        categories: ['1 Jan', '2 Jan', '3 Jan', '4 Jan', '5 Jan', '6 Jan', '7 Jan', '8 Jan', '9 Jan', '10 Jan'],
        labels: {
          style: {
            colors: labelColor,
            fontSize: '13px',
            fontFamily: fontFamily,
            fontWeight: 400
          }
        },
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: {
        tickAmount: 4,
        min: 0,
        max: 50,
        labels: {
          style: {
            colors: labelColor,
            fontSize: '13px',
            fontFamily: fontFamily,
            fontWeight: 400
          },
          formatter: function (val) {
            return val + '%';
          }
        }
      },
      responsive: [
        {
          breakpoint: 1400,
          options: {
            chart: {
              height: 320
            },
            xaxis: {
              labels: {
                style: {
                  fontSize: '10px'
                }
              }
            },
            legend: {
              itemMargin: {
                vertical: 0,
                horizontal: 10
              },
              fontSize: '13px',
              offsetY: 12
            }
          }
        },
        {
          breakpoint: 1025,
          options: {
            chart: { height: 415 },
            plotOptions: { bar: { columnWidth: '50%' } }
          }
        },
        {
          breakpoint: 982,
          options: { plotOptions: { bar: { columnWidth: '30%' } } }
        },
        {
          breakpoint: 480,
          options: {
            chart: { height: 250 },
            legend: { offsetY: 7 }
          }
        }
      ]
    };
  if (typeof shipmentEl !== undefined && shipmentEl !== null) {
    const shipment = new ApexCharts(shipmentEl, shipmentConfig);
    shipment.render();
  }

  // Reasons for delivery exceptions Chart
  // --------------------------------------------------------------------
  const deliveryExceptionsChartE1 = document.querySelector('#deliveryExceptionsChart'),
    deliveryExceptionsChartConfig = {
      chart: {
        height: 396,
        parentHeightOffset: 0,
        type: 'donut'
      },
      labels: ['Incorrect address', 'Weather conditions', 'Federal Holidays', 'Damage during transit'],
      series: [13, 25, 22, 40],
      colors: [
        chartColors.donut.series1,
        chartColors.donut.series2,
        chartColors.donut.series3,
        chartColors.donut.series4
      ],
      stroke: {
        width: 0
      },
      dataLabels: {
        enabled: false,
        formatter: function (val, opt) {
          return parseInt(val) + '%';
        }
      },
      legend: {
        show: true,
        position: 'bottom',
        offsetY: 10,
        markers: {
          size: 5
        },
        itemMargin: {
          horizontal: 15,
          vertical: 5
        },
        fontSize: '13px',
        fontFamily: fontFamily,
        fontWeight: 400,
        labels: {
          colors: headingColor,
          useSeriesColors: false
        }
      },
      tooltip: {
        theme: false
      },
      grid: {
        padding: {
          top: 15
        }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '77%',
            labels: {
              show: true,
              value: {
                fontSize: '24px',
                fontFamily: fontFamily,
                color: headingColor,
                fontWeight: 500,
                offsetY: -20,
                formatter: function (val) {
                  return parseInt(val) + '%';
                }
              },
              name: {
                offsetY: 30,
                fontFamily: fontFamily
              },
              total: {
                show: true,
                fontSize: '15px',
                fontFamily: fontFamily,
                color: legendColor,
                label: 'AVG. Exceptions',
                formatter: function (w) {
                  return '30%';
                }
              }
            }
          }
        }
      },
      responsive: [
        {
          breakpoint: 1025,
          options: {
            chart: {
              height: 380
            }
          }
        }
      ]
    };
  if (typeof deliveryExceptionsChartE1 !== undefined && deliveryExceptionsChartE1 !== null) {
    const deliveryExceptionsChart = new ApexCharts(deliveryExceptionsChartE1, deliveryExceptionsChartConfig);
    deliveryExceptionsChart.render();
  }

  // DataTable (js)
  // --------------------------------------------------------------------
  const dt_dashboard_table = document.querySelector('.dt-route-vehicles');

  // On route vehicles DataTable
  if (dt_dashboard_table) {
    var dt_dashboard = new DataTable(dt_dashboard_table, {
      ajax: assetsPath + 'json/logistics-dashboard.json',
      columns: [
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'location' },
        { data: 'start_city' },
        { data: 'end_city' },
        { data: 'warnings' },
        { data: 'progress' }
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
          render: (data, type, full) => {
            const location = full['location'];

            return `
                  <div class="d-flex justify-content-start align-items-center user-name">
                      <div class="avatar-wrapper">
                          <div class="avatar me-4">
                              <span class="avatar-initial rounded-circle bg-label-secondary">
                                  <i class="icon-base bx bxs-truck icon-lg"></i>
                              </span>
                          </div>
                      </div>
                      <div class="d-flex flex-column">
                          <a class="text-heading text-nowrap fw-medium" href="app-logistics-fleet.html">VOL-${location}</a>
                      </div>
                  </div>
              `;
          }
        },
        {
          targets: 3,
          render: (data, type, full) => {
            const { start_city, start_country } = full;

            return `
                  <div class="text-body">
                      ${start_city}, ${start_country}
                  </div>
              `;
          }
        },
        {
          targets: 4,
          render: (data, type, full) => {
            const { end_city, end_country } = full;

            return `
                  <div class="text-body">
                      ${end_city}, ${end_country}
                  </div>
              `;
          }
        },
        {
          targets: -2,
          render: (data, type, full) => {
            const statusNumber = full['warnings'];
            const status = {
              1: { title: 'No Warnings', class: 'bg-label-success' },
              2: { title: 'Temperature Not Optimal', class: 'bg-label-warning' },
              3: { title: 'Ecu Not Responding', class: 'bg-label-danger' },
              4: { title: 'Oil Leakage', class: 'bg-label-info' },
              5: { title: 'Fuel Problems', class: 'bg-label-primary' }
            };

            const warning = status[statusNumber];

            if (!warning) {
              return data;
            }

            return `
                  <span class="badge rounded ${warning.class}">
                      ${warning.title}
                  </span>
              `;
          }
        },
        {
          targets: -1,
          render: (data, type, full) => {
            const progress = full['progress'];

            return `
                  <div class="d-flex align-items-center">
                      <div class="progress w-100" style="height: 8px;">
                          <div
                              class="progress-bar"
                              role="progressbar"
                              style="width: ${progress}%"
                              aria-valuenow="${progress}"
                              aria-valuemin="0"
                              aria-valuemax="100">
                          </div>
                      </div>
                      <div class="text-body ms-3">${progress}%</div>
                  </div>
              `;
          }
        }
      ],
      select: {
        style: 'multi',
        selector: 'td:nth-child(2)'
      },
      order: [2, 'asc'],
      layout: {
        topStart: {
          rowClass: '',
          features: []
        },
        topEnd: {},
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
      lengthMenu: [5],
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
              return 'Details of ' + data['location'];
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

  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-layout-start', classToAdd: 'my-0' },
      { selector: '.dt-layout-end', classToAdd: 'my-0' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2', classToAdd: 'mt-n2' }
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
