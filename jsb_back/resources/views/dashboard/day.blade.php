@extends('layout')
<!-- 引用模板 -->
@section('head')
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />    
@endsection
@section('content')
<div class="page-content">
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
       <div class="col">
         <div class="card radius-10 border-start border-0 border-3 border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">來客數</p>
                        <h4 class="my-1 text-info" id="orderCount"></h4>
                        <p class="mb-0 font-13"><span id="growthOrderNum"></span> 跟昨日比</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto"><i class='bx bxs-group'></i>
                    </div>
                </div>
            </div>
         </div>
       </div>
       <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-danger">
           <div class="card-body">
               <div class="d-flex align-items-center">
                   <div>
                       <p class="mb-0 text-secondary">當週累計收入</p>
                       <h4 class="my-1 text-danger">$ {{number_format($weekTotal,0,'.',',')}}</h4>
                       {{-- @if ($weekrate>=0)
                        <p class="mb-0 font-13"><span class="text-danger">{{floor($weekrate)}}%</span> 跟上週比</span></p>
                       @else
                        <p class="mb-0 font-13"><span class="text-success">{{floor($weekrate)}}%</span> 跟上週比</span></p>
                       @endif --}}
                   </div>
                   <div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto"><i class='bx bxs-wallet'></i>
                   </div>
               </div>
           </div>
        </div>
      </div>
      <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-success">
           <div class="card-body">
               <div class="d-flex align-items-center">
                   <div>
                       <p class="mb-0 text-secondary">當週進貨成本</p>
                       <h4 class="my-1 text-success">$ {{number_format($costtotal,0,'.',',')}}</h4>
                       <p class="mb-0 font-13"></p>
                   </div>
                   <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bxs-bar-chart-alt-2' ></i>
                   </div>
               </div>
           </div>
        </div>
      </div>
      <div class="col">
            <div class="card radius-10 border-start border-0 border-3 border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">累計支出</p>
                        <h4 class="my-1 text-success">$ {{number_format($expensetotal ,0,'.',',')}}</h4>
                        <p class="mb-0 font-13"></p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bxs-bar-chart-alt-2' ></i>
                    </div>
                </div>
            </div>
            </div>
        </div>
      <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-warning">
           <div class="card-body">
               <div class="d-flex align-items-center">
                   <div>
                       <p class="mb-0 text-secondary">毛利</p>
                       <!--改到後端計算-->
                            <h4 class="my-1 text-warning">
                                $ {{ number_format($grossProfit,0,'.',',') }} 
                                ({{ $grossRate }}%)
                            </h4>
                       
                       <p class="mb-0 font-13"></p>
                   </div>
                   <div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto"><i class='bx bxs-cart'></i>
                   </div>
               </div>
           </div>
        </div>
      </div>

      <div class="col">
        <div class="card radius-10 border-start border-0 border-3 border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">淨利</p>
                                <!--改到後端計算-->
                                <h4 class="my-1 text-warning">
                                    $ {{ number_format($netProfit,0,'.',',') }} 
                                </h4>

                        <p class="mb-0 font-13"></p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto"><i class='bx bxs-bar-chart-alt-2' ></i>
                    <!-- <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bxs-cart'></i> -->
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>

    <div class="row">
       <div class="col-12 col-lg-8">
          <div class="card radius-10">
              <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">時段業績</h6>
                    </div>
                </div>
                <div class="d-flex align-items-center ms-auto font-13 gap-2 my-3">
                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #14abef"></i>今日業績</span>
                    <span class="border px-1 rounded cursor-pointer"><i class="bx bxs-circle me-1" style="color: #ffc107"></i>昨日業績</span>
                </div>
                <div class="chart-container-1">
                    <canvas id="chart1"></canvas>
                  </div>
              </div>
              <div class="row row-group border-top g-0">
                <div class="col">
                    <div class="p-3 text-center">
                        <h4 class="mb-0" style="color: #14abef">NT$ <b id="todayTotal"></b></h4>
                        <p class="mb-0">今日總業績</p>
                    </div>
                </div>
                <div class="col">
                    <div class="p-3 text-center">
                        <h4 class="mb-0" style="color: #ffc107">NT$ <b id="yesterdayTotal"></b></h4>
                        <p class="mb-0">昨日總業績</p>
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
       {{-- <div class="col-12 col-lg-4">
           <div class="card radius-10">
               <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">銷售類別佔比</h6>
                    </div>
                </div>
                <div class="chart-container-2 mt-4">
                    <canvas id="chart2"></canvas>
                  </div>
               </div>
               <ul class="list-group list-group-flush">
                @foreach ($cate as $item)
                <li class="list-group-item d-flex bg-transparent justify-content-between align-items-center">{{$item->cateMainName}} <span class="badge bg-success rounded-pill">{{$item->total}}</span>
                </li>
                @endforeach
            </ul>
           </div>
       </div> --}}
    </div><!--end row-->
    {{-- 商品排行 --}}
     <div class="card radius-10">
             <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">訂單100商品排行</h6>
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
                            <h6>{{$value->productId}}</h6>
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
         <div class="row row-cols-1 row-cols-lg-12">
             <div class="col d-flex">
               <div class="card radius-10 w-100">
                   <div class="card-body">
                    <p class="font-weight-bold mb-1 text-secondary">每周分析</p>
                    {{-- <div class="d-flex align-items-center mb-4">
                        <div>
                            <h4 class="mb-0">$89,540</h4>
                        </div>
                        <div class="">
                            <p class="mb-0 align-self-center font-weight-bold text-success ms-2">4.4% <i class="bx bxs-up-arrow-alt mr-2"></i>
                            </p>
                        </div>
                    </div> --}}
                    <div class="chart-container-0">
                        <canvas id="chart3"></canvas>
                      </div>
                   </div>
               </div>
             </div>
         </div>
         <!--end row-->
         {{-- 近30日業績 --}}
    <div class="card radius-10">
        <div class="card-body">
           <div class="d-flex align-items-center">
               <div>
                   <h6 class="mb-0">近30日業績</h6>
               </div>
               <div class="dropdown ms-auto">
                   <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class='bx bx-dots-horizontal-rounded font-22 text-option'></i>
                   </a>
               </div>
           </div>
        <div class="table-responsive">
           <table class="table table-striped table-bordered">
           <thead>
            <tr>
              <th>日期</th>
              <th>來客數</th>
              <th>總業績</th>
              <th>總售出成本</th>
           
            </tr>
            </thead>
            <tbody>
               @foreach($everydayPerform as $key=>$value)
               <tr>
                   <td>
                       {{$value->order_month}}
                   </td>
                   <td>{{$value->orderNum}}</td>
                   <td>${{number_format($value->totalAmount,0,'.',',')}}</td>
                   <td>${{number_format($value->costtotal,0,'.',',')}}</td>
                 
               </tr>          
               @endforeach
           </tbody>
         </table>
         </div>
        </div>
   </div>

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

<!-- 上傳伺服器記得註解 -->
<script>
    window.route_day = "{{ route('day_chartApi') }}";
    window.route_week = "{{ route('week_chartApi') }}";
</script>

<script src="{{asset('assets/js/chart.js')}}"></script>
<script>$(document).ready(function() {$('#example').DataTable();} );</script>

@endsection
