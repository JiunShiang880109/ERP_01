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
        <div class="breadcrumb-title pe-3">查核類</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{url()->previous()}}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">試算表查詢設定</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2">
                <form method="GET" action="">
                    
                    <div class="d-flex align-items-center gap-3 mb-3 flex-nowrap">
                        <div class="col-auto fw-bold">會計年度</div>
                        <select name="fiscal_year" class="form-select">
                            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ (int)old('fiscal_year', now()->year) === (int)$y ? 'selected' : '' }}>
                                {{ $y }}
                                </option>
                            @endfor
                        </select>

                        <div class="col-auto fw-bold">月份</div>
                        <select name="fiscal_month" class="form-select w-auto">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int)old('fiscal_month', now()->month) === (int)$m ? 'selected' : '' }}>
                                {{ $m }} 月
                                </option>
                            @endfor
                        </select>

                        <button type="submit" class="btn btn-success px-4 text-nowrap">
                            查詢
                        </button>
                    </div>

                </form>
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
                            <td>科目編號</td>
                            <td>科目名稱</td>
                            <td class="text-end">期初借</td>
                            <td class="text-end">期初貸</td>
                            <td class="text-end">本期借</td>
                            <td class="text-end">本期貸</td>
                            <td class="text-end">期末借</td>
                            <td class="text-end">期末貸</td>
                        </tr>
                    </thead>
                    <tbody style="vertical-align:middle;">

                    </tbody>
                  
                </table>
            </div>
        </div>
    </div>

</div>

@if ($errors->any())
        <div id="flashMsg1"
            class="alert alert-danger" 
            style="position: fixed; top:20px; left:50%; transform:translateX(-50%); z-index:9999;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>

        <script>
            setTimeout(()=> document.getElementById('flashMsg1')?.remove(), 10000);
        </script>
    @endif

    @if(session('success') || session('error'))
        <div id="flashMsg"
            class="alert {{ session('success') ? 'alert-success' : 'alert-danger' }}"
            style="position: fixed; top:20px; left:50%; transform:translateX(-50%); z-index:9999;">
            {{ session('success') ?? session('error') }}
        </div>

        <script>
            setTimeout(()=> document.getElementById('flashMsg')?.remove(), 10000);
        </script>
    @endif

@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function () {
        $('#example').DataTable({
        });

    });
</script>

@endsection
