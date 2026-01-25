@extends('layout')
<!-- 引用模板 -->
@section('head')
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
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
        <div class="main">
            <div class="col-lg-12">
                <div class="card alert">
                    {{--  --}}
                    <div class="card-body">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bxs-edit me-1 font-22 text-primary"></i>
                                </div>
                                <h5 class="mb-0 text-primary">訂單明細</h5>
                            </div>
                            <hr>
                            <form class="row g-3">
                                {{--  --}}
                                <div class="col-md-3">
                                    <label for="inputFirstName" class="form-label">訂單號</label>
                                    <h5>{{$orders[0]->orderNum}}</h5>
                                </div>
                                <div class="col-md-3">
                                    <label for="inputLastName" class="form-label">訂單日期</label>
                                    <h5>{{$orders[0]->orderTime}}</h5>
                                </div>
                                <div class="col-md-3">
                                    <label for="inputEmail" class="form-label">總計</label>
                                    <h5>{{$orders[0]->finalPrice}}</h5>
                                </div>
                                <div class="col-md-3">
                                    <label for="inputEmail" class="form-label">發票</label>
                                    <h5>{{$orders[0]->invoiceNumber}}</h5>   
                                </div>
                                {{--  --}}
                                {{-- <div class="col-md-4">
                                    <label for="inputState" class="form-label">訂單狀態</label>
                                    <select id="inputState" class="form-select" name="state">
                                        <option value="0" @if($orders[0]->orderStatus=='0')selected @endif>處理中</option>
                                        <option value="1" @if($orders[0]->orderStatus=='1')selected @endif>已收款出貨</option>
                                    </select>
                                </div> --}}


                                {{-- <div class="col-12">
                                    <button type="submit" class="btn btn-primary px-5">Register</button>
                                </div> --}}
                            </form>
                        
                        </div>
                    {{--  --}}
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width=2% scope="col">#</th>                         
                                    <th width=5% scope="col">品項</th>
                                    <th width=2% scope="col">數量</th>
                                    <th width=2% scope="col">單位</th>
                                    <th width=2% scope="col">單價</th>
                                    <th width=2% scope="col">小計</th>
                                    <th width=2% scope="col">稅別</th>
                                   
                                </tr>
                            </thead>
                            <tbody style="vertical-align:middle;">
                                @foreach ($orders as $key=>$item)
                                <tr>    
                                    <th>{{$key+1}}</th>
                                    <th>{{$item->productName}}</th>
                                    <th>{{$item->quantity}}</th>
                                    <th>{{$item->unit}}</th>
                                    <th>{{$item->unitPrice}}</th>
                                    <th>{{$item->quantity*$item->unitPrice}}</th>
                                    <th>@if($item->taxType==1)<span class="text-primary">含稅</span>@else<span class="text-danger">無稅</span>@endif</th>
                                    
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="text-end">
        <div class="btn-group" role="group" aria-label="First group">
            <button type="button" class="btn btn-outline-primary">1</button>
            <button type="button" class="btn btn-outline-primary">2</button>
            <button type="button" class="btn btn-outline-primary">3</button>
            <button type="button" class="btn btn-outline-primary">4</button>
            <button type="button" class="btn btn-outline-primary">5</button>
        </div>
    </div> --}}


</div>



@endsection
@section('script')
<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#example').DataTable();
      } );
</script>



@endsection