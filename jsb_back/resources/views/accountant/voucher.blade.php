@extends('layout')
<!-- 引用模板 -->
@section('head')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}"
    rel="stylesheet" />
<style>
    .layer {
        background-color: rgba(0, 0, 0, 0.5);
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        z-index: 1;
        width: 160px;
    }

</style>
@endsection
@section('content')
<div class="bg-white p-3">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">傳票管理</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">傳票登錄作業</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />
    
    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2">
                
                <a href="{{route('accountant.add_voucher')}}" class="btn btn-primary ">新增傳票</a>
            
            </div>
        </div>
    </div>

    {{--  --}}
    <div class="col-lg-12">
        <div class="card alert">
            <div class="table-responsive invoice_list">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <td>操作</td>
                            <td>傳票日期</td>
                            <td>傳票編號</td>
                            <td>摘要</td>
                            <td>傳票類別</td>
                            <td>傳票性質</td>
                            <td>員工編號</td>
                        </tr>
                    </thead>
                    <tbody style="vertical-align:middle;">
                       @foreach ($vouchers as $voucher)

                            <tr>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <a href="{{ route('accountant.voucher_detail', $voucher->id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                            查看
                                        </a>
                                        @if($voucher->voucher_kind != 2)
                                            <a href="{{route('accountant.edit_voucher', $voucher->id)}}" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bxs-edit"></i>
                                            </a>
                                            
                                            <form method="POST" action="{{route('accountant.delete_voucher', $voucher->id)}}" class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('確定要刪除此筆傳票？')">
                                                    <i class="bx bxs-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">系統結帳</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{$voucher->voucher_date}}</td>
                                <td>{{$voucher->voucher_code}}</td>
                                <td>{{$voucher->note}}</td>
                                <td>
                                    @switch($voucher->voucher_type)
                                        @case(0) 收入 @break
                                        @case(1) 支出 @break
                                        @case(2) 轉帳 @break
                                    @endswitch
                                </td>
                                <td>
                                    @switch($voucher->voucher_kind)
                                        @case(0)
                                            <span class="badge bg-secondary">一般</span>
                                            @break
                                        @case(1)
                                            <span class="badge bg-warning text-dark">調整</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{$voucher->employeeId}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                  
                </table>
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
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>

<script>
    $(document).ready(function () {
        $('#example').DataTable();
    });

</script>

@endsection
