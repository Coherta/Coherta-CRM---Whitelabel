// time 
function startTime() {
    var today = new Date();
    var h = today.getHours();
    var m = today.getMinutes();
    // var s = today.getSeconds();
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12;
    h = h ? h : 12;
    m = checkTime(m);
    // s = checkTime(s);
    document.getElementById('txt').innerHTML =
        h + ":" + m + ' ' + ampm;
    var t = setTimeout(startTime, 500);
}
function checkTime(i) {
    if (i < 10) { i = "0" + i };  // add zero in front of numbers < 10
    return i;
}

var series = [
    {
        name: 'Contacted',
        data:[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
    },
    {
        name: 'Qualified',
        data:[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
    },
    {
        name: 'Proposal',
        data:[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
    },
    {
        name: 'Negotiation',
        data:[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
    }
];
var options = {
    series: series ,
    chart:{
      type:'bar',
      height:300,
      stacked:true,
      toolbar:{
        show:false,
      },
       dropShadow: {
        enabled: true,
        top: 8,
        left: 0,
        blur: 10,
        color: '#7064F5',
        opacity: 0.1
      }
    }, 
    plotOptions: {
      bar:{       
        horizontal: false,
        columnWidth: '25px',
        borderRadius: 0,
      },
    }, 
    grid: {
      show:true,   
      borderColor: 'var(--chart-border)',               
    },
    dataLabels:{
      enabled: false,
    },
    stroke: {
      width: 2,
      dashArray: 0,
      lineCap: 'butt',
      colors: ["#fff"],
    },
    fill: {
      opacity: 1
    },
    legend: {
      show:false
    },    
    states: {          
      hover: {
        filter: {
          type: 'darken',
          value: 1,
        }
      }           
    },
    colors:['#FF0000', // New
        '#00FF00', // Contacted
        '#0000FF', // Qualified
        '#FFA500', // Proposal
        '#800080'],
    yaxis: {
      tickAmount: 3,   
      labels: {
        show: true,
        style: {
          fontFamily: 'Rubik, sans-serif',
        },
      },  
      axisBorder:{
       show:false,
     },
      axisTicks:{
        show: false,
      },
    },
    xaxis:{     
      categories:[
        '1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18', '19','20','21','22','23','24','25','26','27','28','29','30'
      ],
      labels: {
        style: {
          fontFamily: 'Rubik, sans-serif',
        },
      },
      axisBorder:{
       show:false,
     },
    axisTicks:{
        show: false,
      },
    },
    states: {          
      hover: {
        filter: {
          type: 'darken',
          value: 1,
        }
      }           
    },    
    responsive: [
        {
          breakpoint: 1661,
          options:{
            chart: {
                height: 290,
            }
          }
        },
         {
          breakpoint: 767,
          options:{
            plotOptions: {
              bar:{       
                columnWidth: '35px',
              },
            }, 
             yaxis: {
                  labels: {
                    show: false,
                  }
                }
          }
        },
        {
          breakpoint: 481,
          options:{
            chart: {
                height: 200,
            }
          }
        },
        {
          breakpoint: 420,
          options:{
            chart: {
                height: 170,
            },
            plotOptions: {
              bar:{       
                columnWidth: '40px',
              },
            }, 
          }
        },
      ]    
  };
var chart = new ApexCharts(document.querySelector("#chart-currently"), options);

fetch('https://api.coherta.com/dashboard/overview', {
    method: 'GET',
    headers: {
        'Authorization': `Bearer ${document.body.getAttribute('session_id')}`,
        'Accept': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    // Use Map for faster lookups and avoid repeated Object.keys
    const merged = new Map();
      var wonLeads = 0;

    for (const company of data.companies) {
      const day = company.day;
      const status = JSON.parse(company.overall_lead_status);
      if (!merged.has(day)) merged.set(day, [0, 0, 0, 0, 0]);
      const arr = merged.get(day);
      for (let i = 1; i <= 5; i++) {
        arr[i - 1] += status[i] || 0;
      }
      wonLeads += company.won_deals_today || 0;
    }
    var seriesTotal = 0;
    var seriesInitial = 0;
    var seriesContacted = [];
    var seriesQualified = [];
    var seriesProposal = [];
    var seriesNegotiation = [];
      merged.forEach((s) => {
        seriesContacted.push(s[1]);
        seriesQualified.push(s[2]);
        seriesProposal.push(s[3]);
        seriesNegotiation.push(s[4]);
        if(seriesInitial === 0)
            seriesInitial = (s[0]);
        if(seriesTotal === 0)
            seriesTotal = (s[0] + s[1] + s[2] + s[3] + s[4]);
    });
      series[0].data = seriesContacted;
      series[1].data = seriesQualified;
      series[2].data = seriesProposal;
      series[3].data = seriesNegotiation;
      var options = {
          series: series ,
          chart:{
              type:'bar',
              height:300,
              stacked:true,
              toolbar:{
                  show:false,
              },
              dropShadow: {
                  enabled: true,
                  top: 8,
                  left: 0,
                  blur: 10,
                  color: '#7064F5',
                  opacity: 0.1
              }
          },
          plotOptions: {
              bar:{
                  horizontal: false,
                  columnWidth: '25px',
                  borderRadius: 0,
              },
          },
          grid: {
              show:true,
              borderColor: 'var(--chart-border)',
          },
          dataLabels:{
              enabled: false,
          },
          stroke: {
              width: 2,
              dashArray: 0,
              lineCap: 'butt',
              colors: ["#fff"],
          },
          fill: {
              opacity: 1
          },
          legend: {
              show:false
          },
          states: {
              hover: {
                  filter: {
                      type: 'darken',
                      value: 1,
                  }
              }
          },
          colors:[CubaAdminConfig.primary,  '#FF0000', // New
              '#00FF00', // Contacted
              '#0000FF', // Qualified
              '#FFA500', // Proposal
              '#800080'],
          yaxis: {
              tickAmount: 3,
              labels: {
                  show: true,
                  style: {
                      fontFamily: 'Rubik, sans-serif',
                  },
              },
              axisBorder:{
                  show:false,
              },
              axisTicks:{
                  show: false,
              },
          },
          xaxis:{
              categories:[
                  '1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18', '19','20','21','22','23','24','25','26','27','28','29','30'
              ],
              labels: {
                  style: {
                      fontFamily: 'Rubik, sans-serif',
                  },
              },
              axisBorder:{
                  show:false,
              },
              axisTicks:{
                  show: false,
              },
          },
          states: {
              hover: {
                  filter: {
                      type: 'darken',
                      value: 1,
                  }
              }
          },
          responsive: [
              {
                  breakpoint: 1661,
                  options:{
                      chart: {
                          height: 290,
                      }
                  }
              },
              {
                  breakpoint: 767,
                  options:{
                      plotOptions: {
                          bar:{
                              columnWidth: '35px',
                          },
                      },
                      yaxis: {
                          labels: {
                              show: false,
                          }
                      }
                  }
              },
              {
                  breakpoint: 481,
                  options:{
                      chart: {
                          height: 200,
                      }
                  }
              },
              {
                  breakpoint: 420,
                  options:{
                      chart: {
                          height: 170,
                      },
                      plotOptions: {
                          bar:{
                              columnWidth: '40px',
                          },
                      },
                  }
              },
          ]
      };
      options.xaxis.categories = Array.from(merged.keys());
      $("#total_leads").html(seriesTotal);
      $("#total_initial").html(seriesInitial);
      $("#total_won").html(wonLeads);
      var chart = new ApexCharts(document.querySelector("#chart-currently"), options);
      chart.render();

  })
  .catch(error => console.error('Error:', error));
