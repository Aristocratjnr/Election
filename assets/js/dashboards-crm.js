/**
 * Dashboard CRM
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  let cardColor,
    headingColor,
    labelColor,
    legendColor,
    shadeColor,
    borderColor,
    heatMap1,
    heatMap2,
    heatMap3,
    heatMap4,
    fontFamily;

  if (isDarkStyle) {
    shadeColor = 'dark';
    heatMap1 = '#333457';
    heatMap2 = '#3c3e75';
    heatMap3 = '#484b9b';
    heatMap4 = '#696cff';
  } else {
    shadeColor = '';
    heatMap1 = '#ededff';
    heatMap2 = '#d5d6ff';
    heatMap3 = '#b7b9ff';
    heatMap4 = '#696cff';
  }
  cardColor = config.colors.cardColor;
  headingColor = config.colors.headingColor;
  labelColor = config.colors.textMuted;
  legendColor = config.colors.bodyColor;
  borderColor = config.colors.borderColor;
  fontFamily = config.fontFamily;

  // Donut Chart Colors
  const chartColors = {
    donut: {
      series1: '#66C732',
      series2: '#8DE45F',
      series3: '#AAEB87',
      series4: '#E3F8D7'
    }
  };

  // Radial bar chart functions
  function radialBarChart(color, value) {
    const radialBarChartOpt = {
      chart: {
        height: 55,
        width: 45,
        type: 'radialBar'
      },
      plotOptions: {
        radialBar: {
          hollow: {
            size: '25%'
          },
          dataLabels: {
            show: false
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
          top: -15,
          bottom: -15,
          left: -5,
          right: -15
        }
      },
      series: [value],
      labels: ['Progress']
    };
    return radialBarChartOpt;
  }

  // Progress Chart
  // --------------------------------------------------------------------
  // All progress chart
  const chartProgressList = document.querySelectorAll('.chart-progress');
  if (chartProgressList) {
    chartProgressList.forEach(function (chartProgressEl) {
      const color = config.colors[chartProgressEl.dataset.color],
        series = chartProgressEl.dataset.series;
      const optionsBundle = radialBarChart(color, series);
      const chart = new ApexCharts(chartProgressEl, optionsBundle);
      chart.render();
    });
  }

  // Customer Ratings - Line Charts
  // --------------------------------------------------------------------
  const customerRatingsChartEl = document.querySelector('#customerRatingsChart'),
    customerRatingsChartOptions = {
      chart: {
        height: 212,
        toolbar: { show: false },
        zoom: { enabled: false },
        type: 'line',
        dropShadow: {
          enabled: true,
          enabledOnSeries: [1],
          top: 13,
          left: 4,
          blur: 3,
          color: config.colors.primary,
          opacity: 0.09
        }
      },
      series: [
        {
          name: 'Last Month',
          data: [20, 54, 20, 38, 22, 28, 16, 19, 11]
        },
        {
          name: 'This Month',
          data: [20, 32, 22, 65, 40, 46, 34, 70, 75]
        }
      ],
      stroke: {
        curve: 'smooth',
        dashArray: [8, 0],
        width: [3, 4]
      },
      legend: {
        show: false
      },
      colors: [borderColor, config.colors.primary],
      grid: {
        show: false,
        borderColor: borderColor,
        padding: {
          top: -20,
          bottom: -10,
          left: 0
        }
      },
      markers: {
        size: 6,
        colors: 'transparent',
        strokeColors: 'transparent',
        strokeWidth: 5,
        hover: {
          size: 6
        },
        discrete: [
          {
            fillColor: config.colors.white,
            seriesIndex: 1,
            dataPointIndex: 8,
            strokeColor: config.colors.primary,
            size: 6
          },
          {
            fillColor: config.colors.white,
            seriesIndex: 1,
            dataPointIndex: 3,
            strokeColor: config.colors.black,
            size: 6
          }
        ],
        offsetX: -3
      },
      xaxis: {
        labels: {
          style: {
            colors: labelColor,
            fontSize: '13px'
          }
        },
        axisTicks: {
          show: false
        },
        categories: [' ', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', ' '],
        axisBorder: {
          show: false
        }
      },
      yaxis: {
        show: false
      }
    };
  if (typeof customerRatingsChartEl !== undefined && customerRatingsChartEl !== null) {
    const customerRatingsChart = new ApexCharts(customerRatingsChartEl, customerRatingsChartOptions);
    customerRatingsChart.render();
  }

  // Overview & Sales Activity - Staked Bar Chart
  // --------------------------------------------------------------------
  const salesActivityChartEl = document.querySelector('#salesActivityChart'),
    salesActivityChartConfig = {
      chart: {
        type: 'bar',
        height: 235,
        stacked: true,
        toolbar: {
          show: false
        }
      },
      series: [
        {
          name: 'PRODUCT A',
          data: [75, 50, 55, 60, 48, 82, 59]
        },
        {
          name: 'PRODUCT B',
          data: [25, 29, 32, 35, 34, 18, 30]
        }
      ],
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '40%',
          borderRadius: 9,
          startingShape: 'rounded',
          endingShape: 'rounded',
          borderRadiusApplication: 'around'
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 6,
        lineCap: 'round',
        colors: [cardColor]
      },
      legend: {
        show: false
      },
      colors: [config.colors.danger, config.colors.secondary],
      fill: {
        opacity: 1
      },
      grid: {
        show: false,
        strokeDashArray: 7,
        padding: {
          top: -40,
          left: 0,
          right: 0
        }
      },
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        labels: {
          show: true,
          style: {
            colors: labelColor,
            fontSize: '15px',
            fontFamily: fontFamily
          }
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        show: false
      },
      responsive: [
        {
          breakpoint: 1440,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '50%'
              }
            }
          }
        },
        {
          breakpoint: 1300,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 11,
                columnWidth: '55%'
              }
            }
          }
        },
        {
          breakpoint: 1200,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '45%'
              }
            }
          }
        },
        {
          breakpoint: 1040,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '50%'
              }
            }
          }
        },
        {
          breakpoint: 992,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 12,
                columnWidth: '40%'
              }
            },
            chart: {
              type: 'bar',
              height: 320
            }
          }
        },
        {
          breakpoint: 768,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 11,
                columnWidth: '25%'
              }
            }
          }
        },
        {
          breakpoint: 576,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '35%'
              }
            }
          }
        },
        {
          breakpoint: 440,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 10,
                columnWidth: '45%'
              }
            }
          }
        },
        {
          breakpoint: 360,
          options: {
            plotOptions: {
              bar: {
                borderRadius: 8,
                columnWidth: '50%'
              }
            }
          }
        }
      ],
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
      }
    };
  if (typeof salesActivityChartEl !== undefined && salesActivityChartEl !== null) {
    const salesActivityChart = new ApexCharts(salesActivityChartEl, salesActivityChartConfig);
    salesActivityChart.render();
  }

  // Session Area Chart
  // --------------------------------------------------------------------
  const sessionAreaChartEl = document.querySelector('#sessionsChart'),
    sessionAreaChartConfig = {
      chart: {
        height: 104,
        type: 'area',
        toolbar: {
          show: false
        },
        sparkline: {
          enabled: true
        }
      },
      markers: {
        size: 6,
        colors: 'transparent',
        strokeColors: 'transparent',
        strokeWidth: 4,
        discrete: [
          {
            fillColor: cardColor,
            seriesIndex: 0,
            dataPointIndex: 8,
            strokeColor: config.colors.warning,
            strokeWidth: 2,
            size: 5,
            radius: 8
          }
        ],
        hover: {
          size: 7
        }
      },
      grid: {
        show: false,
        padding: {
          right: 8
        }
      },
      colors: [config.colors.warning],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.4,
          gradientToColors: [config.colors.cardColor],
          opacityTo: 0.4,
          stops: [0, 85, 100]
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        width: 2,
        curve: 'straight'
      },
      series: [
        {
          data: [340, 340, 300, 300, 240, 240, 320, 320, 370]
        }
      ],
      xaxis: {
        show: false,
        lines: {
          show: false
        },
        labels: {
          show: false
        },
        axisBorder: {
          show: false
        }
      },
      yaxis: {
        show: false
      }
    };
  if (typeof sessionAreaChartEl !== undefined && sessionAreaChartEl !== null) {
    const sessionAreaChart = new ApexCharts(sessionAreaChartEl, sessionAreaChartConfig);
    sessionAreaChart.render();
  }

  // Order Statistics Chart
  // --------------------------------------------------------------------
  const leadsReportChartEl = document.querySelector('#leadsReportChart'),
    leadsReportChartConfig = {
      chart: {
        height: 157,
        width: 135,
        parentHeightOffset: 0,
        type: 'donut'
      },
      labels: ['Electronic', 'Sports', 'Decor', 'Fashion'],
      series: [20, 30, 20, 30],
      colors: [
        chartColors.donut.series1,
        chartColors.donut.series4,
        chartColors.donut.series3,
        chartColors.donut.series2
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
          top: 5,
          bottom: 5
        }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '75%',
            labels: {
              show: true,
              value: {
                fontSize: '1.5rem',
                fontFamily: fontFamily,
                color: headingColor,
                fontWeight: 500,
                offsetY: -15,
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
                fontSize: '15px',
                fontFamily: fontFamily,
                label: 'Average',
                color: legendColor,
                formatter: function (w) {
                  return '25%';
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

  // Earning Reports Bar Chart
  // --------------------------------------------------------------------
  const reportBarChartEl = document.querySelector('#reportBarChart'),
    reportBarChartConfig = {
      chart: {
        height: 150,
        type: 'bar',
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          barHeight: '60%',
          columnWidth: '50%',
          startingShape: 'rounded',
          endingShape: 'rounded',
          borderRadius: 4,
          distributed: true
        }
      },
      grid: {
        show: false,
        padding: {
          top: -20,
          bottom: 0,
          left: -10,
          right: -10
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
      colors: [
        config.colors_label.primary,
        config.colors_label.primary,
        config.colors_label.primary,
        config.colors_label.primary,
        config.colors.primary,
        config.colors_label.primary,
        config.colors_label.primary
      ],
      dataLabels: {
        enabled: false
      },
      series: [
        {
          data: [40, 95, 60, 45, 90, 50, 75]
        }
      ],
      legend: {
        show: false
      },
      xaxis: {
        categories: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],
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
      }
    };
  if (typeof reportBarChartEl !== undefined && reportBarChartEl !== null) {
    const barChart = new ApexCharts(reportBarChartEl, reportBarChartConfig);
    barChart.render();
  }

  // Sales Analytics - Heat map chart
  // --------------------------------------------------------------------
  const salesAnalyticsChartEl = document.querySelector('#salesAnalyticsChart'),
    salesAnalyticsChartConfig = {
      chart: {
        height: 350,
        type: 'heatmap',
        parentHeightOffset: 0,
        offsetX: -10,
        toolbar: {
          show: false
        }
      },
      series: [
        {
          name: '1k',
          data: [
            { x: 'Jan', y: '250' },
            { x: 'Feb', y: '350' },
            { x: 'Mar', y: '220' },
            { x: 'Apr', y: '290' },
            { x: 'May', y: '650' },
            { x: 'Jun', y: '260' },
            { x: 'Jul', y: '274' },
            { x: 'Aug', y: '850' }
          ]
        },
        {
          name: '2k',
          data: [
            { x: 'Jan', y: '750' },
            { x: 'Feb', y: '3350' },
            { x: 'Mar', y: '1220' },
            { x: 'Apr', y: '1290' },
            { x: 'May', y: '1650' },
            { x: 'Jun', y: '1260' },
            { x: 'Jul', y: '1274' },
            { x: 'Aug', y: '850' }
          ]
        },
        {
          name: '3k',
          data: [
            { x: 'Jan', y: '375' },
            { x: 'Feb', y: '1350' },
            { x: 'Mar', y: '3220' },
            { x: 'Apr', y: '2290' },
            { x: 'May', y: '2650' },
            { x: 'Jun', y: '2260' },
            { x: 'Jul', y: '1274' },
            { x: 'Aug', y: '815' }
          ]
        },
        {
          name: '4k',
          data: [
            { x: 'Jan', y: '575' },
            { x: 'Feb', y: '1350' },
            { x: 'Mar', y: '2220' },
            { x: 'Apr', y: '3290' },
            { x: 'May', y: '3650' },
            { x: 'Jun', y: '2260' },
            { x: 'Jul', y: '1274' },
            { x: 'Aug', y: '315' }
          ]
        },
        {
          name: '5k',
          data: [
            { x: 'Jan', y: '875' },
            { x: 'Feb', y: '1350' },
            { x: 'Mar', y: '2220' },
            { x: 'Apr', y: '3290' },
            { x: 'May', y: '3650' },
            { x: 'Jun', y: '2260' },
            { x: 'Jul', y: '1274' },
            { x: 'Aug', y: '965' }
          ]
        },
        {
          name: '6k',
          data: [
            { x: 'Jan', y: '575' },
            { x: 'Feb', y: '1350' },
            { x: 'Mar', y: '2220' },
            { x: 'Apr', y: '2290' },
            { x: 'May', y: '2650' },
            { x: 'Jun', y: '3260' },
            { x: 'Jul', y: '1274' },
            { x: 'Aug', y: '815' }
          ]
        },
        {
          name: '7k',
          data: [
            { x: 'Jan', y: '575' },
            { x: 'Feb', y: '1350' },
            { x: 'Mar', y: '1220' },
            { x: 'Apr', y: '1290' },
            { x: 'May', y: '1650' },
            { x: 'Jun', y: '1260' },
            { x: 'Jul', y: '3274' },
            { x: 'Aug', y: '815' }
          ]
        },
        {
          name: '8k',
          data: [
            { x: 'Jan', y: '575' },
            { x: 'Feb', y: '350' },
            { x: 'Mar', y: '220' },
            { x: 'Apr', y: '290' },
            { x: 'May', y: '650' },
            { x: 'Jun', y: '260' },
            { x: 'Jul', y: '274' },
            { x: 'Aug', y: '815' }
          ]
        }
      ],
      plotOptions: {
        heatmap: {
          enableShades: false,
          radius: '6px',
          colorScale: {
            ranges: [
              {
                from: 0,
                to: 1000,
                name: '1k',
                color: heatMap1
              },
              {
                from: 1001,
                to: 2000,
                name: '2k',
                color: heatMap2
              },
              {
                from: 2001,
                to: 3000,
                name: '3k',
                color: heatMap3
              },
              {
                from: 3001,
                to: 4000,
                name: '4k',
                color: heatMap4
              }
            ]
          }
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        width: 4,
        colors: [cardColor]
      },
      legend: {
        show: false
      },
      grid: {
        show: false,
        padding: {
          top: -10,
          left: 16,
          right: -15,
          bottom: 0
        }
      },
      xaxis: {
        labels: {
          show: true,
          style: {
            colors: labelColor,
            fontSize: '15px',
            fontFamily: fontFamily
          }
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        labels: {
          style: {
            colors: labelColor,
            fontSize: '15px',
            fontFamily: fontFamily
          }
        }
      },
      responsive: [
        {
          breakpoint: 1441,
          options: {
            chart: {
              height: '325px'
            },
            grid: {
              padding: {
                right: -15
              }
            }
          }
        },
        {
          breakpoint: 1045,
          options: {
            chart: {
              height: '300px'
            },
            grid: {
              padding: {
                right: -50
              }
            }
          }
        },
        {
          breakpoint: 992,
          options: {
            chart: {
              height: '320px'
            },
            grid: {
              padding: {
                right: -50
              }
            }
          }
        },
        {
          breakpoint: 767,
          options: {
            chart: {
              height: '400px'
            },
            grid: {
              padding: {
                right: 0
              }
            }
          }
        },
        {
          breakpoint: 568,
          options: {
            chart: {
              height: '330px'
            },
            grid: {
              padding: {
                right: -20
              }
            }
          }
        }
      ],
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
      }
    };
  if (typeof salesAnalyticsChartEl !== undefined && salesAnalyticsChartEl !== null) {
    const salesAnalyticsChart = new ApexCharts(salesAnalyticsChartEl, salesAnalyticsChartConfig);
    salesAnalyticsChart.render();
  }

  // Sale Stats - Radial Bar Chart
  // --------------------------------------------------------------------
  const salesStatsEl = document.querySelector('#salesStats'),
    salesStatsOptions = {
      chart: {
        height: 315,
        type: 'radialBar'
      },
      series: [75],
      labels: ['Sales'],
      plotOptions: {
        radialBar: {
          startAngle: 0,
          endAngle: 360,
          strokeWidth: '70',
          hollow: {
            margin: 50,
            size: '75%',
            image: assetsPath + 'img/icons/misc/arrow-star.png',
            imageWidth: 65,
            imageHeight: 55,
            imageOffsetY: -35,
            imageClipped: false
          },
          track: {
            strokeWidth: '50%',
            background: borderColor
          },
          dataLabels: {
            show: true,
            name: {
              offsetY: 60,
              show: true,
              color: legendColor,
              fontSize: '15px',
              fontFamily: fontFamily
            },
            value: {
              formatter: function (val) {
                return parseInt(val) + '%';
              },
              offsetY: 20,
              color: headingColor,
              fontSize: '28px',
              fontWeight: '500',
              fontFamily: fontFamily,
              show: true
            }
          }
        }
      },
      fill: {
        type: 'solid',
        colors: config.colors.success
      },
      stroke: {
        lineCap: 'round'
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
      }
    };
  if (typeof salesStatsEl !== undefined && salesStatsEl !== null) {
    const salesStats = new ApexCharts(salesStatsEl, salesStatsOptions);
    salesStats.render();
  }
});
