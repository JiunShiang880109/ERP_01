@extends('layout')
<!-- 引用模板 -->
@section('head')
@endsection
@section('content')
    <div class="bg-white p-3">
        <div class="row p-2 justify-content-between align-items-center border-bottom">
            <!--breadcrumb-->
           <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3 col-md-6">
               <div class="breadcrumb-title pe-3">支出管理</div>
               <div class="ps-3">
                   <nav aria-label="breadcrumb">
                       <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url()->previous()}}"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{route('expenses.index')}}">支出列表</a></li>
                            <li class="breadcrumb-item active" aria-current="page">新增支出</li>
                       </ol>
                   </nav>
               </div>
           </div>
           <!-- -------------------------- -->
       </div>
       {{--  --}}
        <div class="content-wrap mt-3">
            <div class="main">
                <div class="card alert ">
                        <div class="card-body">
                            {{--{{dd(session()->all())}}--}}
                            <form action="{{ route('expenses.store') }}" enctype="multipart/form-data"  method="POST" class="row g-3">
                                 @csrf
                                 <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bxs-edit me-1 font-22 text-primary"></i>
                                    </div>
                                    <h5 class="mb-0 text-primary">新增支出</h5>
                                </div>
                                <hr>
                                <div class="col-md-4">
                                    <label for="inputFirstName" class="form-label">員工編號</label>
                                    <input type="text" name="employeeId" class="form-control" value="{{session('employeeId')}}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">姓名</label>
                                    <input type="text" name="employeeName" class="form-control" value="{{session('employeeName')}}" readonly>
                                </div>
                                <h6 class="mb-0 text-primary">*員工編號和姓名為當前帳戶之編號及姓名，請注意，勿使用他人帳戶進行操作。</h6>
                                <hr>

                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">日期</label>
                                    <input type="date" name="date" class="form-control" value="">
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">類別</label>
                                    <!-- <input type="text" name="category_main" class="form-control" value=""> -->
                                    <select name="category_main_id" class="form-select">
                                        <option value="">--請選擇類別--</option>
                                        @foreach($categoryMain as $cm)
                                            <option value="{{ $cm->id }}">{{ $cm->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">項目</label>
                                    <input type="text" name="category_sub" class="form-control" value="">
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">金額</label>
                                    <input type="text" name="amount" class="form-control" value="">
                                </div>
                                <div class="col-md-4">
                                    <label for="inputLastName" class="form-label">付款方式</label>
                                    <input type="text" name="payMethod" class="form-control" value="">
                                </div>
                                
                                 {{-- 其他  --}}
                                 <div class="card-title d-flex align-items-center mt-4">
                                    <div><i class="bx bxs-edit me-1 font-22 text-primary"></i>
                                    </div>
                                    <h5 class="mb-0 text-primary">其他</h5>
                                </div>
                                <hr>
                                
                                <div class="col-md-12">
                                    <label for="inputLastName" class="form-label">備註</label>
                                    <input type="text" name="note" class="form-control" value="">
                                </div>
                                
                                {{--  --}}
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary px-5">送出</button>
                                </div>
                                {{--  --}}
                            </form>
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection
