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
            <label for="start">年月份:</label>
            <input type="month" id="nowYearMonth" name="nowYearMonth" onchange="loginAnalysisDataInsert()">
        </div>

        <!-- 後台登入分析 -->
        <div class="col-12 row">
            <h6 class="mb-0 text-uppercase">後台登入分析</h6>
            <hr/>
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-body">
                        <div id="backStageLoginAnalysis"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card">
                    <div class="card-body" style="overflow-y: scroll;height: 408px;">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr class="text-center">
                                    <th scope="col" width="20%">ip</th>
                                    <th scope="col" width="40%">登入時間</th>
                                    <th scope="col" width="40%">登出時間</th>
                                </tr>
                            </thead>
                            <tbody  id="backStageLoginAnalysisDetail">
                                <tr class="text-center">
                                    <td>192.168.0.110</td>
                                    <td>2022-05-03 11:11:00</td>
                                    <td>2022-05-03 11:11:00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
<!-- <script>$(document).ready(function() {$('#example').DataTable();} );</script> -->

<script>
    //日期初始化
    let nowYear = new Date().getFullYear()
    let nowMonth = (new Date().getMonth() + 1) < 10 ? '0' + (new Date().getMonth() + 1) : (new Date().getMonth() + 1)
    let nowYearMonth = nowYear + '-' + nowMonth
    $('input#nowYearMonth').val(nowYearMonth)

    let nowYearMonthsssss = $('input#nowYearMonth').val()
    let dateData = nowYearMonthsssss.split("-")

    // 後台登入分析圖 
    const backStageAnalysis = (daysArray, backStageMaxCount, backStageArray) => {
        var options = {
            /*************************************直方圖樣式********************************* */
            // 直方圖標題
            title: {
                text: '後台登入',
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
                    columnWidth: '30%',
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
            colors: ["#ffc107"],
            /*************************************直條樣式********************************* */

            /*************************************x、y軸參數********************************* */
            // x軸
            xaxis: {
                categories: daysArray,
            },
            // y軸 min->最小值、max->最大值、tickAmount->最小值跟最大值中間要分幾格 labels->讓y軸顯示整數或是浮點數
            yaxis: {
                min:0,
                max: backStageMaxCount,
                tickAmount: Math.floor(backStageMaxCount/5),
                title: {
                    text: '登入次數'
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
            series: backStageArray,
            // 軸線裡的內容(滑鼠一上去才看的到)
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + "次"
                    }
                }
            },
            /*************************************直方圖資料內容********************************* */
        };
        var chart = new ApexCharts(document.querySelector("#backStageLoginAnalysis"), options);
        chart.render();
    }

    /*****************************************取資料並組合************************************** */
    // 先產出月份天數陣列
    const computeMonthDays = async () => {
        // 取得現在選擇時間
        let nowYearMonth = $('input#nowYearMonth').val()
        let dateData = nowYearMonth.split("-")
        let year = dateData[0]
        let month = dateData[1]

        let date = new Date(year, month, 0)
        return date.getDate()
    }

    // 取得登入分析資料
    const getLoginAnalysis = async () => {
        // 取得現在選擇時間
        let nowYearMonth = $('input#nowYearMonth').val()
        let dateData = nowYearMonth.split("-")
        let year = dateData[0]
        let month = dateData[1]

        let response = await axios.post("{{route('loginAnalysis')}}",{
            'year': year,
            'month': month
        })

        return response.data.loginAnalysis
    }

    // 組合分析要的資料
    const loginAnalysisData = async () => {
        // 取這個月
        let days = await computeMonthDays()
        // 登入分析資料
        let loginAnalysiss = await getLoginAnalysis()

        // 天數陣列組合
        let daysArray = []
        let dataArray = []
        for (d = 1; d <= days; d++) {
            daysArray.push(`${d}`)
            dataArray.push(0)
        }

        let backStageArray = [] // 宣告後台空陣列        

        backStageData = [...dataArray]

        let backStageMaxCount = 0

        loginAnalysiss.forEach((loginAnalysis) => {
            if(loginAnalysis.loginLocation == 2){
                let day = loginAnalysis.day
                let loginLocation = loginAnalysis.loginLocation
                let loginCount = Math.floor(loginAnalysis.loginCount)            

                backStageData[day-1] = loginCount //將對應日期的資料塞進去
                if(backStageMaxCount < loginCount){
                    backStageMaxCount = loginCount  //y軸的級距
                }
            }
            
        })

        backStageMaxCount = Math.ceil(backStageMaxCount/5) * 5  //y軸的級距 5次為一個分界

        backStageArray.push({
            name: '後台登入紀錄',
            data: backStageData
        })

        return [{
           daysArray, backStageMaxCount, backStageArray
        }]        
    }

    // 取得登入詳細時間資料
    const getloginAnalysisDetail = async () => {
        // 取得現在選擇時間
        let nowYearMonth = $('input#nowYearMonth').val()
        let dateData = nowYearMonth.split("-")
        let year = dateData[0]
        let month = dateData[1]

        let response = await axios.post("{{route('loginAnalysisDetail')}}",{
            'year': year,
            'month': month
        })

        return response.data.loginAnalysisDetail
    }

    // 組合登入詳細時間資料
    const loginAnalysisDetailHtml = async () => {
        let loginAnalysisDetails = await getloginAnalysisDetail()
        let backStageDetail = ``

        loginAnalysisDetails.forEach((loginAnalysisDetail)=>{
            let loginLocation = loginAnalysisDetail.loginLocation
            let ip = loginAnalysisDetail.ip
            let loginTime = loginAnalysisDetail.loginTime
            let logOutTime = loginAnalysisDetail.logOutTime

            if(loginAnalysisDetail.loginLocation == 2){
                backStageDetail += `<tr class="text-center">
                                    <td>${ip}</td>
                                    <td>${loginTime}</td>
                                    <td>${logOutTime}</td>
                                </tr>`
            }
            
        })
        return backStageDetail
    }

    // 登入資料塞入直方圖中
    const loginAnalysisDataInsert = async () => {
        $('#backStageLoginAnalysis').html('')
        $('tBody#backStageLoginAnalysisDetail').html('')

        // 取登入分析資料
        let data = await loginAnalysisData()

        // 塞後台登入直方圖
        backStageAnalysis(data[0].daysArray, data[0].backStageMaxCount, data[0].backStageArray)

        let html = await loginAnalysisDetailHtml()
        console.log(html);
        $('tBody#backStageLoginAnalysisDetail').html(html)
    }

    loginAnalysisDataInsert()


</script>

@endsection