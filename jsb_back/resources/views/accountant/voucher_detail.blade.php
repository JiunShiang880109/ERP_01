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
                    <li class="breadcrumb-item"><a href="{{url()->previous()}}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{route('accountant.voucher')}}">傳票登錄作業</a></li>
                    <li class="breadcrumb-item active" aria-current="page">傳票分錄</li>
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
                            <td>傳票編號</td>
                            <td>科目編號</td>
                            <td>科目名稱</td>
                            <td>摘要</td>
                            <td>金額</td>
                            <td>借/貸</td>
                            <td>傳票類別</td>
                            <td>員工編號</td>
                        </tr>
                    </thead>
                    <tbody style="vertical-align:middle;">
                        @foreach ($items as $item)
                            <tr>
                                <td>-</td>

                                {{-- 傳票編號 --}}
                                <td>{{ $voucher->voucher_code }}</td>

                                {{-- 科目編號 --}}
                                <td>
                                    {{ $item->main_code }}{{ $item->sub_code }}{{ $item->item_code }}.{{ $item->ledger_code }}
                                </td>

                                {{-- 科目名稱（子科目名稱） --}}
                                <td>{{ $item->item_name}}-{{$item->ledger_name ?? ''}}</td>

                                {{-- 摘要 --}}
                                <td>{{ $item->note }}</td>

                                {{-- 金額 --}}
                                <td class="text-end">
                                    {{ number_format($item->amount, 2) }}
                                </td>

                                {{-- 借 / 貸 --}}
                                <td>{{ $item->dc }}</td>

                                {{-- 傳票類別 --}}
                                <td>
                                    @switch($voucher->voucher_type)
                                        @case(0) 收入 @break
                                        @case(1) 支出 @break
                                        @case(2) 轉帳 @break
                                    @endswitch
                                </td>

                                {{-- 員工編號 --}}
                                <td>{{ $voucher->employeeId }}</td>
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
