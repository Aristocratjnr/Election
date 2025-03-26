/**
 * Academy Dashboard charts and datatable
 */

'use strict';

// Hour pie chart

document.addEventListener('DOMContentLoaded', function (e) {
  let labelColor, headingColor, borderColor, fontFamily, legendColor;
  labelColor = config.colors.textMuted;
  headingColor = config.colors.headingColor;
  borderColor = config.colors.borderColor;
  fontFamily = config.fontFamily;
  legendColor = config.colors.bodyColor;

  // Donut Chart Colors
  const chartColors = {
    donut: {
      series1: 'color-mix(in sRGB, ' + config.colors.success + ' 80%, ' + config.colors.black + ')',
      series2: 'color-mix(in sRGB, ' + config.colors.success + ' 90%, ' + config.colors.black + ')',
      series3: config.colors.success,
      series4: 'color-mix(in sRGB, ' + config.colors.success + ' 80%, ' + config.colors.cardColor + ')',
      series5: 'color-mix(in sRGB, ' + config.colors.success + ' 60%, ' + config.colors.cardColor + ')',
      series6: 'color-mix(in sRGB, ' + config.colors.success + ' 40%, ' + config.colors.cardColor + ')'
    }
  };

  const leadsReportChartEl = document.querySelector('#leadsReportChart'),
    leadsReportChartConfig = {
      chart: {
        height: 170,
        width: 150,
        parentHeightOffset: 0,
        type: 'donut'
      },
      labels: ['36h', '56h', '16h', '32h', '56h', '16h'],
      series: [23, 35, 10, 20, 35, 23],
      colors: [
        chartColors.donut.series1,
        chartColors.donut.series2,
        chartColors.donut.series3,
        chartColors.donut.series4,
        chartColors.donut.series5,
        chartColors.donut.series6
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
        show: false
      },
      tooltip: {
        theme: false
      },
      grid: {
        padding: {
          top: 0
        }
      },
      states: {
        hover: {
          filter: {
            type: 'none'
          }
        },
        active: {
          filter: {
            type: 'none'
          }
        }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '70%',
            labels: {
              show: true,
              value: {
                fontSize: '1.125rem',
                fontFamily: fontFamily,
                color: headingColor,
                fontWeight: 500,
                offsetY: -20,
                formatter: function (val) {
                  return parseInt(val) + '%';
                }
              },
              name: {
                offsetY: 20,
                fontFamily: fontFamily
              },
              total: {
                show: true,
                fontSize: '.9375rem',
                fontFamily: fontFamily,
                label: 'Total',
                color: labelColor,
                formatter: function (w) {
                  return '231h';
                }
              }
            }
          }
        }
      }
    };
  if (typeof leadsReportChartEl !== undefined && leadsReportChartEl !== null) {
    const leadsReportChart = new ApexCharts(leadsReportChartEl, leadsReportChartConfig);
    leadsReportChart.render();
  }

  // datatbale bar chart

  const horizontalBarChartEl = document.querySelector('#horizontalBarChart'),
    horizontalBarChartConfig = {
      chart: {
        height: 360,
        type: 'bar',
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          horizontal: true,
          barHeight: '60%',
          distributed: true,
          startingShape: 'rounded',
          borderRadiusApplication: 'end',
          borderRadius: 7
        }
      },
      grid: {
        strokeDashArray: 10,
        borderColor: borderColor,
        xaxis: {
          lines: {
            show: true
          }
        },
        yaxis: {
          lines: {
            show: false
          }
        },
        padding: {
          top: -35,
          bottom: -12
        }
      },

      colors: [
        config.colors.primary,
        config.colors.info,
        config.colors.success,
        config.colors.secondary,
        config.colors.danger,
        config.colors.warning
      ],
      fill: {
        opacity: [1, 1, 1, 1, 1, 1]
      },
      dataLabels: {
        enabled: true,
        style: {
          colors: ['#fff'],
          fontWeight: 400,
          fontSize: '13px',
          fontFamily: fontFamily
        },
        formatter: function (val, opts) {
          return horizontalBarChartConfig.labels[opts.dataPointIndex];
        },
        offsetX: 0,
        dropShadow: {
          enabled: false
        }
      },
      labels: ['UI Design', 'UX Design', 'Music', 'Animation', 'React', 'SEO'],
      series: [
        {
          data: [35, 20, 14, 12, 10, 9]
        }
      ],

      xaxis: {
        categories: ['6', '5', '4', '3', '2', '1'],
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        },
        labels: {
          style: {
            colors: labelColor,
            fontFamily: fontFamily,
            fontSize: '13px'
          },
          formatter: function (val) {
            return `${val}%`;
          }
        }
      },
      yaxis: {
        max: 35,
        labels: {
          style: {
            colors: [labelColor],
            fontFamily: fontFamily,
            fontSize: '13px'
          }
        }
      },
      tooltip: {
        enabled: true,
        style: {
          fontSize: '12px'
        },
        onDatasetHover: {
          highlightDataSeries: false
        },
        custom: function ({ series, seriesIndex, dataPointIndex, w }) {
          return '<div class="px-3 py-2">' + '<span>' + series[seriesIndex][dataPointIndex] + '%</span>' + '</div>';
        }
      },
      legend: {
        show: false
      }
    };
  if (typeof horizontalBarChartEl !== undefined && horizontalBarChartEl !== null) {
    const horizontalBarChart = new ApexCharts(horizontalBarChartEl, horizontalBarChartConfig);
    horizontalBarChart.render();
  }

  //radial Barchart

  function radialBarChart(color, value, show) {
    const radialBarChartOpt = {
      chart: {
        height: show == 'true' ? 60 : 55,
        width: show == 'true' ? 58 : 45,
        type: 'radialBar'
      },
      plotOptions: {
        radialBar: {
          hollow: {
            size: show == 'true' ? '50%' : '25%'
          },
          dataLabels: {
            show: show == 'true' ? true : false,
            value: {
              offsetY: -10,
              fontSize: '15px',
              fontWeight: 500,
              fontFamily: fontFamily,
              color: headingColor
            }
          },
          track: {
            background: config.colors_label.secondary
          }
        }
      },
      stroke: {
        lineCap: 'round'
      },
      colors: [color],
      grid: {
        padding: {
          top: show == 'true' ? -12 : -15,
          bottom: show == 'true' ? -17 : -15,
          left: show == 'true' ? -17 : -5,
          right: -15
        }
      },
      series: [value],
      labels: show == 'true' ? [''] : ['Progress']
    };
    return radialBarChartOpt;
  }

  const chartProgressList = document.querySelectorAll('.chart-progress');
  if (chartProgressList) {
    chartProgressList.forEach(function (chartProgressEl) {
      const color = config.colors[chartProgressEl.dataset.color],
        series = chartProgressEl.dataset.series;
      const progress_variant = chartProgressEl.dataset.progress_variant;
      const optionsBundle = radialBarChart(color, series, progress_variant);
      const chart = new ApexCharts(chartProgressEl, optionsBundle);
      chart.render();
    });
  }

  // datatable

  // Variable declaration for table
  const dt_academy_course = document.querySelector('.datatables-academy-course'),
    logoObj = {
      angular:
        '<span class="badge bg-label-danger rounded p-1_5"><i class="icon-base bx bxl-angular icon-28px"></i></span>',
      figma:
        '<span class="badge bg-label-warning rounded p-1_5"><i class="icon-base bx bxl-figma icon-28px"></i></span>',
      react: '<span class="badge bg-label-info rounded p-1_5"><i class="icon-base bx bxl-react icon-28px"></i></span>',
      art: '<span class="badge bg-label-success rounded p-1_5"><i class="icon-base bx bxs-color icon-28px"></i></span>',
      fundamentals:
        '<span class="badge bg-label-primary rounded p-1_5"><i class="icon-base bx bx-diamond icon-28px"></i></span>'
    };

  if (dt_academy_course) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0', 'text-nowrap', 'text-md-start', 'text-center');
    tableTitle.innerHTML = 'Course you are taking';
    let dt_course = new DataTable(dt_academy_course, {
      ajax: assetsPath + 'json/app-academy-dashboard.json',
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'course name' },
        { data: 'time' },
        { data: 'progress' },
        { data: 'status' }
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
          responsivePriority: 2,
          render: (data, type, full) => {
            const { logo, course, user, image } = full;

            const output = image
              ? `<img src="${assetsPath}img/avatars/${image}" alt="Avatar" class="rounded-circle">`
              : (() => {
                  // Generate initials and random state for badge
                  const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
                  const state = states[Math.floor(Math.random() * states.length)];
                  const initials = (user.match(/\b\w/g) || []).reduce((acc, char) => acc + char.toUpperCase(), '');
                  return `<span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>`;
                })();

            // Create full row output
            return `
                  <div class="d-flex align-items-center">
                      <span class="me-4">${logoObj[logo]}</span>
                      <div>
                          <a class="text-heading text-truncate fw-medium mb-2 text-wrap" href="app-academy-course-details.html">
                              ${course}
                          </a>
                          <div class="d-flex align-items-center mt-1">
                              <div class="avatar-wrapper me-2">
                                  <div class="avatar avatar-xs">
                                      ${output}
                                  </div>
                              </div>
                              <small class="text-nowrap text-heading">${user}</small>
                          </div>
                      </div>
                  </div>
              `;
          }
        },
        {
          targets: 3,
          responsivePriority: 3,
          render: data => {
            const duration = moment.duration(data);
            const hours = Math.floor(duration.asHours());
            const minutes = Math.floor(duration.asMinutes()) - hours * 60;
            const formattedTime = `${hours}h ${minutes}m`;

            return `<span class="fw-medium text-nowrap text-heading">${formattedTime}</span>`;
          }
        },
        {
          targets: 4,
          render: (data, type, full) => {
            const { status: statusNumber, number: averageNumber } = full;

            return `
                  <div class="d-flex align-items-center gap-3">
                      <p class="fw-medium mb-0 text-heading">${statusNumber}</p>
                      <div class="progress w-100" style="height: 8px;">
                          <div
                              class="progress-bar"
                              style="width: ${statusNumber}"
                              aria-valuenow="${statusNumber}"
                              aria-valuemin="0"
                              aria-valuemax="100">
                          </div>
                      </div>
                      <small>${averageNumber}</small>
                  </div>
              `;
          }
        },
        {
          targets: 5,
          render: (data, type, full) => {
            const { user_number: userNumber, note, view } = full;

            return `
                  <div class="d-flex align-items-center justify-content-between">
                      <div class="w-px-75 d-flex align-items-center">
                          <i class="icon-base bx bx-user icon-lg me-1_5 text-primary"></i>
                          <span>${userNumber}</span>
                      </div>
                      <div class="w-px-75 d-flex align-items-center">
                          <i class="icon-base bx bx-book-open icon-lg me-1_5 text-info"></i>
                          <span>${note}</span>
                      </div>
                      <div class="w-px-75 d-flex align-items-center">
                          <i class="icon-base bx bx-video icon-lg me-1_5 text-danger"></i>
                          <span>${view}</span>
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
      order: [[2, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row card-header border-bottom mx-0 px-3 py-2',
          features: [tableTitle]
        },
        topEnd: {
          search: {
            placeholder: 'Search Course',
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
      lengthMenu: [5],
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
              table.classList.add('datatables-basic');
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
  // ? setTimeout used for data-table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' }
    ];

    // Delete record
    elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
      document.querySelectorAll(selector).forEach(element => {
        classToRemove.split(' ').forEach(className => element.classList.remove(className));
        if (classToAdd) {
          element.classList.add(classToAdd);
        }
      });
    });
  }, 100);
});
