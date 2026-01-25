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
    $.ajax({
      // url:"{{route('day_chartApi')}}",
      // url: `${window.location.protocol}//${window.location.host}/${window.FOLDER}dashboard/mon_chartApi/`,
      //上傳伺服器記得改回上面這行
      url:window.route_month,
      type: "get",
	    async: false,
      dataType: "json",   
      success: function(Jdata) {  
       
      var mon = Jdata['mon']; 
      const today_totalAmount = []; 
      const yesterday_totalAmount = []; 
      let todayTotal =Math.floor(Jdata['todayTotal'][0]['total']);  
      let yesterdayTotal =Math.floor(Jdata['yesterdayTotal'][0]['total']);  
       //今日總業績
       document.getElementById("todayTotal").innerHTML = numberWithCommas(todayTotal);
       //昨日總業績
       document.getElementById("yesterdayTotal").innerHTML = numberWithCommas(yesterdayTotal);

      var NumOfJData = Jdata['today'].length;
      for (var i = 0; i < NumOfJData; i++) {

        today_totalAmount.push(Jdata['today'][i]['totalAmount']);//今日業績
        yesterday_totalAmount.push(Jdata['yesterday'][i]['totalAmount']);//昨日業績
      }
      
      var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: mon,
          datasets: [{
            label: '今年',
            data: today_totalAmount,
            borderColor: gradientStroke1,
            backgroundColor: gradientStroke1,
            hoverBackgroundColor: gradientStroke1,
            pointRadius: 0,
            fill: false,
            borderWidth: 0
          }, {
            label: '去年',
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
// chart 5 來客數

var ctx = document.getElementById("chart5").getContext('2d');
   
var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
gradientStroke1.addColorStop(0, '#f54ea2');
gradientStroke1.addColorStop(1, '#ff7676');

var gradientStroke2 = ctx.createLinearGradient(0, 0, 0, 300);
gradientStroke2.addColorStop(0, '#42e695');
gradientStroke2.addColorStop(1, '#3bb2b8');
$.ajax({
  // url:"{{route('day_chartApi')}}",
  // url: `${window.location.protocol}//${window.location.host}/${window.FOLDER}dashboard/mon_chartApi/`,
  //上傳伺服器記得改回上面這行
  url:window.route_month_num,
  type: "get",
  async: false,
  dataType: "json",   
  success: function(Jdata) {  
        var mon = Jdata['mon']; 
        const today_orderNum = []; 
        const yesterday_orderNum = []; 
        let todayTotal =Math.floor(Jdata['todayTotal'][0]['orderNum']);  
        let yesterdayTotal =Math.floor(Jdata['yesterdayTotal'][0]['orderNum']);  
         //今日總業績
        $('#todayNumTotal').text(todayTotal);
        $('#yesterdayNumTotal').text(yesterdayTotal);


        var NumOfJData = Jdata['today'].length;
        for (var i = 0; i < NumOfJData; i++) {
          today_orderNum.push(Jdata['today'][i]['orderNum']);//今日業績
          yesterday_orderNum.push(Jdata['yesterday'][i]['orderNum']);//昨日業績
        }


        var myChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: mon,
            datasets: [{
              label: '今年來客數',
              data: today_orderNum,
              borderColor: gradientStroke1,
              backgroundColor: gradientStroke1,
              hoverBackgroundColor: gradientStroke1,
              pointRadius: 0,
              fill: false,
              borderWidth: 1
            }, {
              label: '去年來客數',
              data: yesterday_orderNum,
              borderColor: gradientStroke2,
              backgroundColor: gradientStroke2,
              hoverBackgroundColor: gradientStroke2,
              pointRadius: 0,
              fill: false,
              borderWidth: 1
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
        scales: {
          xAxes: [{
          barPercentage: .5
          }]
          },
        tooltips: {
          displayColors:false,
        }
        }
        });


  }
});
//






});	 
   