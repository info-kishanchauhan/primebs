 var options = {
            series: [{
                name: 'Projects',
                data: [70, 75, 90, 96, 87, 110]
            }, {
                name: 'Day',
                data: [76, 85, 101, 98, 87, 105]
            }],
            chart: {
            height: 330,
            type: 'area'
          },
          fill: {
            colors: ['#FC5A69', '#8A88FF']
          },

          dataLabels: {
            enabled: false,
            colors: ['#FC5A69', '#8A88FF']
          },
          markers: {
            colors: ['#FC5A69', '#8A88FF']
         },
          stroke: {
            curve: 'smooth',
            width: 1,
            colors: ['#FC5A69', '#8A88FF']
           
          },
          legend: {
            show: false,
          },
          xaxis: {
            categories: ['S', 'M', 'T', 'W', 'T', 'F','S'],
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            categories: ['0', '20', '40', '60', '80', '100'],
            
        },
        
          };
  
          var chart = new ApexCharts(document.querySelector("#chart1"), options);
          chart.render();
        
          var options = {
            series: [70],
            chart: {
            height: 300,
            type: 'radialBar',
          },
          fill: {
            colors: ['#8A88FF'],
             opacity: 1,
          },

          plotOptions: {
            radialBar: {
                dataLabels: {
                    name: {
                        show: false,
                        fontWeight: '700'
                    },
                    value: {
                      fontSize: '30px',
                      fontWeight:'bold'
                    },
                },
              hollow: {
                size: '60%',
              }
            },
          },
          labels: ['Attendance'],
          };
  
          var chart = new ApexCharts(document.querySelector("#chart21"), options);
          chart.render();

          var options = {
            series: [90],
            chart: {
            height: 300,
            type: 'radialBar',
          },
          fill: {
            colors: ['#8A88FF'],
            opacity: 1,
          },
          plotOptions: {
            radialBar: {
                dataLabels: {
                    name: {
                        show: false,
                        fontWeight: '700'
                    },
                    value: {
                      fontSize: '30px',
                      fontWeight:'bold'
                    },
                },
              hollow: {
                size: '60%',
              }
            },
          },
          labels: ['Attendance'],
          };
  
          var chart = new ApexCharts(document.querySelector("#chart31"), options);
          chart.render();
          
