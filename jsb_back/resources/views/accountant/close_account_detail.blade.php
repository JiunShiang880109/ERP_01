@extends('layout')
<!-- 引用模板 -->
@section('head')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}"
    rel="stylesheet" />
<style>
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
                    <li class="breadcrumb-item"><a href="{{url()->previous()}}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{route('accountant.category')}}">科目類別管理</a></li>
                    <li class="breadcrumb-item"><a href="{{route('accountant.close_account')}}">關帳設定</a></li>
                    <li class="breadcrumb-item active" aria-current="page">關帳紀錄</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    {{--  --}}
    <div class="col-lg-12">
        <div class="card alert">
            <div class="table-responsive invoice_list">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <td>操作</td>
                            <td>關帳年份</td>
                            <td>關帳月份</td>
                            <td>關帳日期</td>
                            <td>摘要</td>
                            <td>員工編號</td>
                        </tr>
                    </thead>
                    <tbody style="vertical-align:middle;">
                        @foreach($logs as $row)
                            <tr>
                                <td>
                                    @if(
                                        !$yearClosed &&
                                        $row->fiscal_month !== null &&
                                        $row->fiscal_year == now()->year &&
                                        $row->fiscal_month == now()->month
                                    )
                                        <form action="{{route('accountant.reopen_close_account')}}" method="POST" 
                                            onsubmit="return confirm('{{$row->fiscal_month}}已月結，確定要重開?')">
                                            @csrf
                                            <input type="hidden" name="fiscal_year" value="{{$row->fiscal_year}}">
                                            <input type="hidden" name="fiscal_month" value="{{$row->fiscal_month}}">
                                            <button type="submit" class="btn btn-warning btn-sm">
                                                重開月結
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{$row->fiscal_year}}</td>
                                <td>{{$row->fiscal_month ? $row->fiscal_month.'月' : '年結'}}</td>
                                <td>{{optional($row->closed_at)->format('Y-m-d')}}</td>
                                <td>{{$row->note}}</td>
                                <td>{{$row->employeeId}}</td>
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
