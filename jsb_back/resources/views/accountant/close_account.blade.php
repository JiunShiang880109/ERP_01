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
                    <li class="breadcrumb-item active" aria-current="page">關帳設定</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2">

                <a href="{{route('accountant.close_account_detail')}}"
                class="btn btn-primary">
                    關帳紀錄
                </a>

            </div>
        </div>
    </div>

    {{--  --}}
    <div class="content-wrap mt-3">
        <div class="main">
            <div class="card alert ">
                    <div class="card-body">
                        {{--{{dd(session()->all())}}--}}
                        <form action="{{route('accountant.store_close_account')}}" method="POST">
                            @csrf

                            <div class="row justify-content-center">
                                {{-- 控制整體寬度 --}}
                                <div class="col-md-6 col-lg-5">

                                    <div class="row g-3">

                                        <div class="col-12">
                                            <label class="form-label">員工編號</label>
                                            <input type="text"
                                                name="employeeId"
                                                class="form-control"
                                                value="{{ session('employeeId') }}"
                                                readonly>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">會計年度</label>
                                            <select name="fiscal_year" class="form-select">
                                            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                                <option value="{{ $y }}" {{ (int)old('fiscal_year', now()->year) === (int)$y ? 'selected' : '' }}>
                                                {{ $y }}
                                                </option>
                                            @endfor
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">關帳類型</label>
                                            <select name="closing_type" id="closing_type" class="form-select">
                                            <option value="month"
                                                {{ old('closing_type', session('need_confirm') ? 'year' : 'month') === 'month' ? 'selected' : '' }}>
                                                月結
                                            </option>
                                            <option value="year"
                                                {{ old('closing_type', session('need_confirm') ? 'year' : 'month') === 'year' ? 'selected' : '' }}>
                                                年結
                                            </option>
                                            </select>
                                        </div>

                                        <div class="col-12" id="month_block">
                                            <label class="form-label">關帳月份</label>
                                            <select name="fiscal_month" class="form-select">
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" {{ (string)$m === (string)old('fiscal_month', 1) ? 'selected' : '' }}>
                                                {{ $m }} 月
                                                </option>
                                            @endfor
                                            </select>

                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">備註</label>
                                            <textarea name="note"
                                                class="form-control"
                                                rows="3"></textarea>
                                        </div>
                                        @if(session('need_confirm'))
                                            <input type="hidden" name="closing_type" value="year">
                                            <div class="alert alert-warning mt-3">
                                                <strong>尚有未完成月結的月份：</strong>
                                                {{ implode('、', session('missingMonths', [])) }}<br>
                                                是否由系統自動補齊月結並完成年結？
                                            </div>

                                            {{-- 使用者確認後才會送出 --}}
                                            <input type="hidden" name="force_close_months" value="1">

                                            <div class="col-12 text-center mt-4 d-flex justify-content-center gap-3">
                                                <button type="submit"
                                                    class="btn btn-danger px-4">
                                                    確認補齊月結並年結
                                                </button>

                                                <a href="{{ route('accountant.close_account') }}"
                                                class="btn btn-secondary px-4">
                                                    取消
                                                </a>
                                            </div>
                                        @else
                                            <div class="col-12 text-center mt-4">
                                                <button type="submit"
                                                    class="btn btn-success px-5">
                                                    確認關帳
                                                </button>
                                            </div>
                                        @endif
                                        {{--  --}}

                                    </div>

                                </div>
                            </div>
                        </form>
                        
                    </div>
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
        $('#closeAccount').DataTable({
        });

    });
</script>
<script>
    $(function(){
        function toggleMonth(){
            if($('#closing_type').val() ==='year'){
                $('#month_block').hide();
            }else{
                $('#month_block').show();
            }
        }
        toggleMonth();

        $('#closing_type').on('change', toggleMonth);
    });
    
</script>

@endsection
