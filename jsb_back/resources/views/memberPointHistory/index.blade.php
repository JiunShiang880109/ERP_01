@extends('layout')
<!-- 引用模板 -->
@section('head')
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
@endsection
@section('content')
<div class="page-content">
    <div class="row">.
        <!-- 時間選擇器 -->
        <div class="col-xl-8 ">
            <label for="start">年月日:</label>
            <input type="date" id="date" name="date" onchange="memberPointHistory()">
        </div>

        <!-- 點數紀錄分析 -->
        <div class="col-12 row mt-3">
            <h6 class="mb-0 text-uppercase">點數紀錄</h6>
            <hr />
            <div class="col-xl-12 p-0">
                <div class="card">
                    <div class="card-body">
                        <div id="monthDayPointSumHistogram"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 點數紀錄body -->
        <div class="col-xl-12 row">
            <div class="card">
                <div class="card-body row">
                    <div class="col-6 border-end">
                        <div class="col-12">
                            <span class="fs-3">當月點數結算 : <span id="monthPointSum"></span></span>
                        </div>
                        <div class="col-12">
                            <span class="fs-5">當月回饋點數 : <span id="monthLivePointSum"></span></span>
                        </div>
                        <div class="col-12">
                            <span class="fs-5">當月使用點數 : <span id="monthCarryPointSum"></span></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="col-12">
                            <span class="fs-3">當日點數結算 : <span id="dayPointSum"></span></span>
                        </div>
                        <div class="col-12">
                            <span class="fs-5">當日回饋點數 : <span id="dayLivePointSum"></span></span>
                        </div>
                        <div class="col-12">
                            <span class="fs-5">當日使用點數 : <span id="dayCarryPointSum"></span></span>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        <!-- 點數紀錄table -->
        <div class="col-xl-12 row">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered" id="memberPointHistory">
                        <thead>
                            <tr>
                                <th scope="col" width="25%">時間</th>
                                <th scope="col" width="25%">訂單編號</th>
                                <th scope="col" width="25%">回饋</th>
                                <th scope="col" width="25%">使用</th>
                            </tr>
                        </thead>
                        <tbody id="memberPointHistoryData">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
@section('script')
<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>

<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js')}}"></script>
<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js')}}"></script>
<script src="{{asset('assets/plugins/chartjs/js/Chart.min.js')}}"></script>
<script src="{{asset('assets/plugins/chartjs/js/Chart.extension.js')}}"></script>
<script src="{{asset('assets/js/index.js')}}"></script>
<script src="{{asset('assets/js/chart.js')}}"></script>
<script src="{{asset('assets/plugins/apexcharts-bundle/js/apexcharts.min.js')}}"></script>

<script>
    let nowYear = new Date().getFullYear()
    let nowMonth = (new Date().getMonth() + 1) < 10 ? '0' + (new Date().getMonth() + 1) : (new Date().getMonth() + 1)
    let nowDay = (new Date().getDate()) < 10 ? '0' + (new Date().getDate()) : (new Date().getDate())
    let nowDate = nowYear + '-' + nowMonth + '-' + nowDay
    $('input#date').val(nowDate)

    //點數紀錄分析圖 
    const pointHistogram = (daysArray,pointArray) => {
        var options = {
            /*************************************直方圖樣式********************************* */
            // 直方圖標題
            title: {
                text: '點數紀錄',
                align: 'left',
                style: {
                    fontSize: '20px'
                }
            },
            // foreColor xy軸、title顏色、height 整個圖的寬度
            chart: {
                foreColor: '#9ba7b2',
                type: 'bar',
                height: 360
            },
            // horizontal樣式 true->橫的 、false->直的  columnWidth寬度  endingShape直條圖弧度
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '40%',
                    endingShape: 'rounded'
                },
            },
            /*************************************直方圖樣式********************************* */

            /*************************************直條樣式********************************* */
            // 個直線數字顯示 ture->顯示、false->不顯示
            dataLabels: {
                enabled: false
            },
            // 直條邊框樣式
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            // 直條顏色透明度
            fill: {
                opacity: 1
            },
            // 直條顏色 顏色會輪流 假設顏色有3個 直條圖有4個 那第4個直條圖的顏色就會回到第一個顏色去輪
            colors: ["#0d6efd","#CC0000"],
            /*************************************直條樣式********************************* */

            /*************************************x、y軸參數********************************* */
            // x軸
            xaxis: {
                categories: daysArray,
            },
            // y軸
            yaxis: {
                title: {
                    text: '點數紀錄'
                },
                labels: {
                    formatter: function(val) {
                        return Math.floor(val)
                    }
                },
            },
            /*************************************x、y軸參數********************************* */

            /*************************************直方圖資料內容********************************* */
            // 直條圖數量 name->名字、data->資料
            series: pointArray,
            // 軸線裡的內容(滑鼠一上去才看的到)
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + "點"
                    }
                }
            },
            /*************************************直方圖資料內容********************************* */
        };
        var chart = new ApexCharts(document.querySelector("#monthDayPointSumHistogram"), options);
        chart.render();
    }

    // /*****************************************取資料並組合************************************** */
    // 先產出月份天數陣列
    const computeMonthDays = async () => {
        // 取得現在選擇時間
        let nowMonth = $('input#date').val()
        let dateData = nowMonth.split("-")
        let year = dateData[0]
        let month = dateData[1]
        let date = new Date(year, month, 0)
        return date.getDate()
    }

    /*****************************************撈點數紀錄資料************************************** */
    // 取得點數紀錄資料
    const getMemberPointHistory = async () => {
        let date = $('input#date').val()
        let response = await axios.post("{{route('memberPointHistory')}}",{
            'date': date
        })
        return response.data.memberPointHistory
    }

    // 組合memberPointHistory table要得html
    const memberPointHistoryHtml = async (memberPointHistorys) => {
        // 產生table要的資料
        let html = ``
        memberPointHistorys.forEach((memberPointHistory)=>{
            let livePoint = memberPointHistory.livePoint ? memberPointHistory.livePoint : 0
            let carryPoint = memberPointHistory.carryPoint ? memberPointHistory.carryPoint : 0
            html += `<tr>
                                           <th scope="row">${memberPointHistory.created_at}</th>
                                           <td>${memberPointHistory.orderNum}</td>
                                           <td>${livePoint}</td>
                                           <td>${carryPoint}</td>
                                       </tr>`
        })
        return html
    }

    // 點數紀錄質方圖資料組合
    const monthDayPointSumHistogram = async (monthDayPointSums) => {
        $('#monthDayPointSumHistogram').html(``)
        // 取這個月
        let days = await computeMonthDays()
        console.log(days);

        //天數陣列組合
        let daysArray = []
        let livePointData = []
        let carryPointData = []

        for (d = 1; d <= days; d++) {
            daysArray.push(`${d}`)
            livePointData.push(0)
            carryPointData.push(0)
        }

        let yMaxCount = 0
        monthDayPointSums.forEach((monthDayPointSum)=>{
            let livePoint = monthDayPointSum.livePoint
            let carryPoint = monthDayPointSum.carryPoint

            livePointData[monthDayPointSum.day-1] = livePoint ? Math.floor(livePoint) : 0
            carryPointData[monthDayPointSum.day-1] = carryPoint ? Math.floor(carryPoint) : 0

            maxPointCount = livePoint >= carryPoint ? livePoint : carryPoint
            yMaxCount = yMaxCount > maxPointCount ? yMaxCount : maxPointCount
        })

        let pointArray = []

        pointArray.push({
            name: '回饋點數',
            data: livePointData
        })

        pointArray.push({
            name: '使用點數',
            data: carryPointData
        })

        let histogram = pointHistogram(daysArray,pointArray)
    }

    // 塞點數紀錄資料
    const memberPointHistory = async () => {
        let data = await getMemberPointHistory()
        let memberPointHistorys = data.history
        let tableHtml = await memberPointHistoryHtml(memberPointHistorys)
        let monthDayPointSum = data.monthDayPointSum
        monthDayPointSumHistogram(monthDayPointSum)

        // 將資料塞入
        $("span#monthPointSum").html(data.monthPointSum)
        $("span#monthLivePointSum").html(data.monthLivePointSum)
        $("span#monthCarryPointSum").html(data.monthCarryPointSum)
        $("span#dayPointSum").html(data.dayPointSum)
        $("span#dayLivePointSum").html(data.dayLivePointSum)
        $("span#dayCarryPointSum").html(data.dayCarryPointSum)
        $("tBody#memberPointHistoryData").html(tableHtml)
    }
    memberPointHistory()


</script>

@endsection