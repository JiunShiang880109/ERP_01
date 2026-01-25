@extends('layout')
<!-- 引用模板 -->
@section('head')
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />    
@endsection
@section('content')
<div class="page-content">

    <div class="row">
       <div class="col-12 col-lg-8">
          <div class="card radius-10">
              <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">每月業績</h6>
                    </div>
                </div>
                <div class="d-flex align-items-center ms-auto font-13 gap-2 my-3">
                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #14abef"></i>今年總業績</span>
                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #ffc107"></i>去年總業績</span>
                </div>
                <div class="chart-container-1">
                    <canvas id="chart1"></canvas>
                  </div>
              </div>
              <div class="row row-group border-top g-0">
                <div class="col">
                    <div class="p-3 text-center">
                        <h4 class="mb-0" style="color: #14abef">NT$ <b id="todayTotal"></b></h4>
                        <p class="mb-0">今年總業績</p>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3 text-center">
                        <h4 class="mb-0" style="color: #ffc107">NT$ <b id="yesterdayTotal"></b></h4>
                        <p class="mb-0">去年總業績</p>
                    </div>
                 </div>
            </div>
              {{-- <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3 g-0 row-group text-center border-top">
                <div class="col">
                  <div class="p-3">
                    <h5 class="mb-0">24.15M</h5>
                    <small class="mb-0">Overall Visitor <span> <i class="bx bx-up-arrow-alt align-middle"></i> 2.43%</span></small>
                  </div>
                </div>
                <div class="col">
                  <div class="p-3">
                    <h5 class="mb-0">12:38</h5>
                    <small class="mb-0">Visitor Duration <span> <i class="bx bx-up-arrow-alt align-middle"></i> 12.65%</span></small>
                  </div>
                </div>
                <div class="col">
                  <div class="p-3">
                    <h5 class="mb-0">639.82</h5>
                    <small class="mb-0">Pages/Visit <span> <i class="bx bx-up-arrow-alt align-middle"></i> 5.62%</span></small>
                  </div>
                </div>
              </div>  --}}
          </div>
       </div>
       {{-- 長柱圖 --}}
       <div class="col-12 col-lg-4">
         <div class="col d-flex">
           <div class="card radius-10 w-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">每月來客數</h6>
                    </div>
                </div>
                <div class="d-flex align-items-center ms-auto font-13 gap-2 my-3">
                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #f54ea2"></i>今年來客數</span>
                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #42e695"></i>去年來客數</span>
                </div>
                <div class="chart-container-1">
                    <canvas id="chart5"></canvas>
                  </div>
              </div>

               <div class="row row-group border-top g-0">
                   <div class="col">
                       <div class="p-3 text-center">
                           <h4 class="mb-0 text-danger"><b id="todayNumTotal"></b></h4>
                           <p class="mb-0">今年來客數</p>
                       </div>
                   </div>
                   <div class="col">
                       <div class="p-3 text-center">
                           <h4 class="mb-0 text-success"><b id="yesterdayNumTotal"></b></h4>
                           <p class="mb-0">去年來客數</p>
                       </div>
                    </div>
               </div><!--end row-->
           </div>
         </div>
    </div><!--end row-->
       {{--  --}}
    </div><!--end row-->
    {{-- 業績 --}}
    <div class="card radius-10">
        <div class="card-body">
           <div class="d-flex align-items-center">
               <div>
                   <h6 class="mb-0">每月業績</h6>
               </div>
               <div class="dropdown ms-auto">
                   <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                   </a>
               </div>
           </div>
        <div class="table-responsive">
           <table class="table table-striped">
           <thead>
            <tr>
              <th>月份</th>  
              <th>來客數</th>
              <th>業績</th>
            </tr>
            </thead>
            <tbody>
               @foreach($today as $key=>$value)
               <tr> 
                   <td>{{$value->order_month}}月</td>
                   <td>{{$value->orderNum}}</td>
                   <td>${{number_format($value->totalAmount,0,'.',',')}}</td>
               </tr>          
               @endforeach
           </tbody>
         </table>
         </div>
        </div>
   </div>
    {{-- 訂單500商品排行 --}}
     <div class="card radius-10">
             <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">訂單500商品排行</h6>
                    </div>
                    <div class="dropdown ms-auto">
                        <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                        </a>
                    </div>
                </div>
             <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered">
                <thead>
                 <tr>
                   <th>排行</th>  
                   <th>商品</th>
                   <th>單價</th>
                   <th>數量</th>
                   <th>銷售金額</th>
                 </tr>
                 </thead>
                 <tbody>
                    @foreach($pd as $key=>$value)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td>
                            {{$value->product_title}}
                            <h6 class="text-primary">{{$value->productId}}</h6>
                        </td>
                        <td>{{$value->unitPrice}}</td>
                        <td>{{$value->quantity}}</td>
                        <td>${{number_format($value->sumfinalPrice,0,'.',',')}}</td>
                    </tr>          
                    @endforeach
                </tbody>
              </table>
              </div>
             </div>
        </div>
    {{--  --}}


</div>


@endsection
@section('script')
<!-- 上傳伺服器需註解 -->
<script src="{{asset('assets/js/jquery.min.js')}}"></script>

<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>

<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js')}}"></script>
<script src="{{asset('assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js')}}"></script>
<script src="{{asset('assets/plugins/chartjs/js/Chart.min.js')}}"></script>
<script src="{{asset('assets/plugins/chartjs/js/Chart.extension.js')}}"></script>
<script src="{{asset('assets/js/index.js')}}"></script>
<!-- 上傳伺服器需註解 -->
<script>
    window.route_month = "{{ route('mon_chartApi') }}";
    window.route_month_num = "{{ route('mon_chartApi') }}";
</script>

<script src="{{asset('assets/js/monchart.js')}}"></script>
<script>$(document).ready(function() {$('#example').DataTable();} );</script>

@endsection
