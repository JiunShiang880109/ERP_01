$(function() {
    "use strict";
    // 當日來店業績
  var ctx = document.getElementById("chart1").getContext('2d');
  var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
      gradientStroke1.addColorStop(0, '#6078ea');  
      gradientStroke1.addColorStop(1, '#17c5ea'); 
  var gradientStroke2 = ctx.createLinearGradient(0, 0, 0, 300);
      gradientStroke2.addColorStop(0, '#ff8359');
      gradientStroke2.addColorStop(1, '#ffdf40');
      //alert(`${window.location.protocol}//${window.location.host}/${window.FOLDER}dashboard/day_chartApi/`);
      //console.log( `${window.location.protocol}//${window.location.host}/${window.FOLDER}dashboard/day_chartApi/`);
    $.ajax({
       //url: `${window.API_URL}${window.FOLDER}dashboard/day_chartApi/`,
      //url: `${window.location.protocol}//${window.location.host}/${window.FOLDER}dashboard/day_chartApi/`,
      //上傳伺服器記得改回上面這行
      url: window.route_day,
      type: "get",
	    async: false,
      dataType: "json",   
      success: function(Jdata) {
      //今日總業績
      let todayTotal =Math.floor(Jdata['todayTotal'][0]['total']);
 
      let yesterdayTotal =Math.floor(Jdata['yesterdayTotal'][0]['total']);
     
    
      //今日訂單數量
      let todey_orderNum = Jdata['todayTotal'][0]['orderNum'];
      let yesterday_orderNum = Jdata['yesterdayTotal'][0]['orderNum'];
      document.getElementById("orderCount").innerHTML = numberWithCommas(todey_orderNum);
      //(當期數據-以前數據）/以前數據
      let growthOrderNum = Math.floor(((todey_orderNum-yesterday_orderNum) / yesterday_orderNum)*100);
      if(growthOrderNum>=0){
        $('#growthOrderNum').addClass('text-danger').text('+'+growthOrderNum+'%');
      }else{
        $('#growthOrderNum').addClass('text-success').text(growthOrderNum+'%');
      }
      //訂單成長百分比

      //今日總業績
      document.getElementById("todayTotal").innerHTML = numberWithCommas(todayTotal);
      //昨日總業績
      document.getElementById("yesterdayTotal").innerHTML = numberWithCommas(yesterdayTotal);

      const today_order_hour = [];
      const today_totalAmount = [];
      const yesterday_totalAmount = [];
      const today_orderNum = [];
      var NumOfJData = Jdata['today'].length;
      for (var i = 0; i < NumOfJData; i++) {
        today_order_hour.push(Jdata['today'][i]['order_hour']+'時');//時數
        today_totalAmount.push(Jdata['today'][i]['totalAmount']);//今日業績
        yesterday_totalAmount.push(Jdata['yesterday'][i]['totalAmount']);//昨日業績
       
      }
     
      
      var myChart = new Chart(ctx, {
        type: 'bar',
            data: {
              labels: today_order_hour,
              datasets: [{
                label: '今日業績',
                data: today_totalAmount,
                borderColor: gradientStroke1,
                backgroundColor: gradientStroke1,
                hoverBackgroundColor: gradientStroke1,
                pointRadius: 0,
                fill: false,
                borderWidth: 0
              }, {
                label: '昨日業績',
                data: yesterday_totalAmount,
                borderColor: gradientStroke2,
                backgroundColor: gradientStroke2,
                hoverBackgroundColor: gradientStroke2,
                pointRadius: 0,
                fill: false,
                borderWidth: 0
              }]
            },
        
        options:{
          maintainAspectRatio: false,
          legend: {
            position: 'bottom',
                  display: false,
            labels: {
                    boxWidth:8
                  }
                },
          tooltips: {
            displayColors:false,
          },	
          scales: {
            xAxes: [{
            barPercentage: .5
            }]
            }
        }
      });
     
      
      //每周分析END
       

      },

});
////////////////////////////時段業績end
// chart 2 類別業績
// $.ajax({
//   url: `${window.location.protocol}//${window.location.host}/${window.FOLDER}dashboard/cate_chartApi/`,
//   type: "get",
//   async: false,
//   dataType: "json",   
//   success: function(Jdata) {
//     const cateMainName = [];
//     const cateTotal = [];
//     var NumOfweektotal = Jdata['cate'].length;
//     for (var i = 0; i < NumOfweektotal; i++) {
//       cateMainName.push(Jdata['cate'][i]['cateMainName']);
//       cateTotal.push(Jdata['cate'][i]['total']);
//     }


// var ctx = document.getElementById("chart2").getContext('2d');

// var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
//     gradientStroke1.addColorStop(0, '#fc4a1a');
//     gradientStroke1.addColorStop(1, '#f7b733');

// var gradientStroke2 = ctx.createLinearGradient(0, 0, 0, 300);
//     gradientStroke2.addColorStop(0, '#4776e6');
//     gradientStroke2.addColorStop(1, '#8e54e9');


// var gradientStroke3 = ctx.createLinearGradient(0, 0, 0, 300);
//     gradientStroke3.addColorStop(0, '#ee0979');
//     gradientStroke3.addColorStop(1, '#ff6a00');
  
// var gradientStroke4 = ctx.createLinearGradient(0, 0, 0, 300);
//     gradientStroke4.addColorStop(0, '#42e695');
//     gradientStroke4.addColorStop(1, '#3bb2b8');
// var gradientStroke5 = ctx.createLinearGradient(0, 0, 0, 300);
//     gradientStroke5.addColorStop(0, '#14abef');
//     gradientStroke5.addColorStop(1, '#14abef');
//     var myChart = new Chart(ctx, {
//       type: 'doughnut',
//       data: {
//         labels: cateMainName,
//         datasets: [{
//           backgroundColor: [
//             gradientStroke1,
//             gradientStroke2,
//             gradientStroke3,
//             gradientStroke4,
//             gradientStroke5
//           ],
//           hoverBackgroundColor: [
//             gradientStroke1,
//             gradientStroke2,
//             gradientStroke3,
//             gradientStroke4,
//             gradientStroke5
//           ],
//           data:cateTotal,
//           borderWidth: [1, 1, 1, 1]
//         }]
//       },
//       options: {
//     maintainAspectRatio: false,
//     cutoutPercentage: 75,
//           legend: {
//       position: 'bottom',
//             display: false,
//       labels: {
//               boxWidth:8
//             }
//           },
//     tooltips: {
//       displayColors:false,
//     }
//       }
//     });
//   }
 
// });
//類別業績end
 // chart 3 每周分析
 $.ajax({
  //url: `${window.location.protocol}//${window.location.host}/${window.FOLDER}dashboard/week_chartApi/`,
  //上傳伺服器記得改回上面這行
  url: window.route_week,
  type: "get",
  async: false,
  dataType: "json",   
  success: function(Jdata) {
    
 var ctx = document.getElementById('chart3').getContext('2d');
 var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
     gradientStroke1.addColorStop(0, '#008cff');
     gradientStroke1.addColorStop(1, 'rgba(22, 195, 233, 0.1)');

  const weektotal = [];
  const weekUsedCost = Jdata['weekUsedCostArr']; // 本週售出成本
  const weekExpense = Jdata['weekExpense']; // 本週支出
  const grossProfit = Jdata['dailyGrossProfit']; // 毛利
  const netProfit = Jdata['dailyNetProfit']; // 淨利

  var NumOfweektotal = Jdata['weektotal'].length;
  for (var i = 0; i < NumOfweektotal; i++) {
  // weektotal.push(Jdata['weektotal'][i][0]['total']);
    weektotal.push(Jdata['weektotal'][i]);
  }


     var myChart = new Chart(ctx, {
       type: 'line',
       data: {
         labels: ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
         datasets: [{
           label: '收入',
           data: weektotal,
           pointBorderWidth: 2,
           pointHoverBackgroundColor: gradientStroke1,
           backgroundColor: gradientStroke1,
           borderColor: gradientStroke1,
           borderWidth: 3
         },
        {
          label: '售出成本',
          data: weekUsedCost,
          borderColor: '#ff6384',
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          borderWidth:2,
          borderDash:[5,5],
          tension:0.3
        },
        {
          label: '支出',
          data:weekExpense,
          borderColor: '#ff9f40',
          backgroundColor: 'rgba(255, 159, 64, 0.2)',
          borderWidth:2,
          borderDash:[5,5],
          tension:0.3
        },
        {
          label: '毛利',
          data: grossProfit,
          borderColor: '#9966ff',
          backgroundColor: 'rgba(153,102,255,0.2)',
          borderWidth: 2,
          tension: 0.3
        },
        {
          label: '淨利',
          data: netProfit,
          borderColor: '#4bc0c0',
          backgroundColor: 'rgba(75,192,192,0.2)',
          borderWidth: 2,
          tension: 0.3
        }]
       },
       options: {
     maintainAspectRatio: false,
           legend: {
            position: 'bottom',
            display:false
           },
           tooltips: {
       displayColors:false,	
             mode: 'nearest',
             intersect: false,
             position: 'nearest',
             xPadding: 10,
             yPadding: 10,
             caretPadding: 10
           }
        }
     });    
     //END
    }
 
  });

   });	 
   