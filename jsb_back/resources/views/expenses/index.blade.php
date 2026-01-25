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
            <div class="breadcrumb-title pe-3">支出管理</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item" aria-current="page">支出列表</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- -------------------------- -->
        <div class="col-md-2 col-12 text-end addProduct">
            
            <a href="{{route('expenses.add')}}" class="btn btn-primary ">新增支出</a>
        </div>
    </div>
    {{--  --}}
    <div class="content-wrap mt-3">
        <div class="main">
            <div class="col-lg-12">
                <div class="card alert">
                    <div class="table-responsive invoice_list">
                        <table id="example" style="text-align:center;"
                            class="center table table-hover js-basic-example dataTable table-custom spacing8">
                            <thead>
                                <tr>
                                    
                                    <th scope="col">日期</th>
                                    <th>類別</th>
                                    <th>項目</th>
                                    <th scope="col">金額</th>
                                    <th>付款方式</th>
                                    <th>備註</th>
                                    <th>員工編號</th>
                                    <th>登記人</th>
                                    <th>操作</th>
                                    </tr>
                                </tr>
                            </thead>
                            <tbody style="vertical-align:middle;text-align: center;">
                                @foreach($expenses as $e)
                                    <tr>
                                        <td>{{ $e->date }}</td>
                                        <td>{{ $e->categoryMain->name ?? '未設定類別' }}</td>
                                        <td>{{ $e->category_sub }}</td>
                                        <td>${{ number_format($e->amount, 0) }}</td>
                                        <td>{{ $e->payMethod }}</td>
                                        <td>{{ $e->note }}</td>
                                        <td>{{ $e->employeeId }}</td>
                                        <td>{{ $e->employeeName }}</td>
                                        <td>
                                            <div class="d-flex order-actions">
                                                <!-- <a href="{{--{{route('category_edit',['cateId'=>$value->id])}}--}}" class=""><i class="bx bxs-edit"></i></a> -->
                                                <a href="{{route('expenses.edit', ['id' => $e->id])}}" class="me-3 text-primary"><i class="bx bxs-edit"></i></a>
                                                
                                                <form action="{{ route('expenses.delete', $e->id) }}" method="POST" onsubmit="return confirm('確定要刪除該筆資料嗎?');" class="m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link p-0 text-danger">
                                                        <i class="bx bxs-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success') || session('error'))
    <div id="flashMsg"
        class="alert {{ session('success') ? 'alert-success' : 'alert-danger' }}"
        style="position: fixed; top:20px; left:50%; transform:translateX(-50%); z-index:9999;">
        {{ session('success') ?? session('error') }}
    </div>

    <script>
        setTimeout(()=> document.getElementById('flashMsg')?.remove(), 2000);
    </script>
@endif


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
<script>$(document).ready(function() {$('#example').DataTable();} );</script>

@endsection
