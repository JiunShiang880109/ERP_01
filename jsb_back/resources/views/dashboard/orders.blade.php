@extends('layout')
<!-- 引用模板 -->
@section('head')
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />    
<link rel="stylesheet" href="https://saas1.3cc.cc/css/datepicker.css">
@endsection
@section('content')
    <div class="bg-white p-3">
        <div class="row p-2 justify-content-between align-items-center border-bottom">
             <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3 col-md-6">
                <div class="breadcrumb-title pe-3">儀錶板</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item" aria-current="page"> 訂單查詢</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="content-wrap mt-3">
            <div class="main" id="computed_props">
                {{--  --}}
                <div class="row">
                    {{--  --}}
                    <form action="{{route('orders')}}" method="post">  
                        @csrf
                    <div style="margin:20px 0px;float:left;">
                        <div class="c-datepicker-date-editor  J-datepicker-range-day">
                        <i class="c-datepicker-range__icon kxiconfont icon-clock"></i>
                        <input placeholder="開始日期" value="{{@$startdate}}" autocomplete="off" name="starttime" class="c-datepicker-data-input only-date" required>
                        <span class="c-datepicker-range-separator">-</span>
                        <input placeholder="結束日期" value="{{@$enddate}}" autocomplete="off" name="endtime" class="c-datepicker-data-input only-date" required>
                        </div>
                        <button type="submit" class="btn btn-primary" style="position:relative;top: -2px">搜尋</button>
                    </div>   
                    </form>
                    {{--  --}}
             
                <div class="col-lg-12">
                    <div class="card alert">
                        {{-- <h6 class="mb-0 text-primary font-20">{{$value}}</h6>
                        <h6 class="mb-0 font-20" style="position:absolute;right:15px;">總計：$ {{number_format($compute[$key],0,'.',',')}}</h6> --}}
                        <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>單號</th>
                                <th>消費時間</th>
                                <th>金額</th>
                                <th>明細</th>
                            </tr>
                            </thead>
                            <tbody>    
                                @foreach ($orders as $key=>$value)

                                @php
                                    $useTypeKey = $value->useType;
                                    $badgeClass = $useType_color[$useTypeKey] ?? 'text-secondary bg-light-secondary';
                                    $useTypeName = $useType_array[$useTypeKey] ?? '未分類';
                                @endphp

                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>
                                        <div class="badge rounded-pill {{$badgeClass}} p-2 text-uppercase px-3">
                                            <i class='bx bxs-circle me-1'></i>
                                            {{$value->seatId}} {{$useTypeName}}{{$value->buyNumber}}號</div>
                                    </td>
                                    <td>{{$value->orderTime}}</td>
                                    <td>$ {{number_format($value->finalPrice,0,'.',',')}}</td>
                                   
                                    <td>
                                        <div class="d-flex order-actions">
                                            <a href="{{route('orders_detail',['orderNum'=>$value->orderNum])}}" class=""><i class="bx bxs-edit"></i></a>
                                            
                                        </div>
                                    </td>
                                </tr>  
                                @endforeach    
                            </tbody>                          
                        </table>
                        </div>
                        <!-- 大類別 -->
                    </div>
                </div>
                
                </div>
                {{--  --}}



            </div>
        </div>
    </div>


@endsection
@section('script')
<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
{{-- <script src="{{asset('assets/js/vue.js')}}"></script> --}}
{{-- <script src="{{asset('assets/js/axios.min.js')}}"></script> --}}
<script>$(document).ready(function() {$('#example').DataTable();} );</script>

<script src="{{asset('assets/datepicker/moment.min.js')}}"></script>
<script src="{{asset('assets/datepicker/datepicker.all.js')}}"></script>
<script src="{{asset('assets/datepicker/index.js')}}"></script>

{{-- <script>
var vm = new Vue({
    el:'#computed_props',
    data:{
        totalPrice:1
    },
    methods:{
       
    },
    computed:{
        
    },
    watch:{
       
    }

});
</script> --}}
@endsection
