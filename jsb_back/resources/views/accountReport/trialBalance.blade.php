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
                    <li class="breadcrumb-item active" aria-current="page">試算表查詢</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2">
                <form method="GET" action="{{route('accountReport.trialBalance')}}">
                    
                    <div class="d-flex align-items-center gap-3 mb-3 flex-nowrap">
                        <div class="col-auto fw-bold">會計年度</div>
                        <select name="fiscal_year" class="form-select">
                            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>
                                {{ $y }}
                                </option>
                            @endfor
                        </select>

                        <div class="col-auto fw-bold">月份</div>
                        <select name="fiscal_month" class="form-select w-auto">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int)$month === (int)$m ? 'selected' : '' }}>
                                {{ $m }} 月
                                </option>
                            @endfor
                        </select>

                        <div class="col-auto fw-bold">試算方式</div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input"
                                   type="radio"
                                   name="mode"
                                   value="period"
                                   {{request('mode', 'accumulate') === 'period' ? 'checked' : ''}}>
                            <label class="form-check-label">本期</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input"
                                   type="radio"
                                   name="mode"
                                   value="accumulate"
                                   {{request('mode', 'accumulate') === 'accumulate' ? 'checked' : ""}}>
                            <label class="form-check-label">累計</label>
                        </div>
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
                        {{--{{dd($rows)}}--}}
                        
                        @forelse($rows as $row)
                            <tr>
                                <td>-</td>
                                <td>{{$row->account_code}}</td>
                                <td>{{$row->account_name}}</td>
                                <td class="text-end">{{number_format($row->opening_debit, 2)}}</td>
                                <td class="text-end">{{number_format($row->opening_credit, 2)}}</td>
                                <td class="text-end">{{number_format($row->period_debit, 2)}}</td>
                                <td class="text-end">{{number_format($row->period_credit, 2)}}</td>
                                <td class="text-end">{{number_format($row->ending_debit, 2)}}</td>
                                <td class="text-end">{{number_format($row->ending_credit, 2)}}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    尚無資料
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr class="fw-bold table-secondary">
                            {{-- 前三欄合併 --}}
                            <td colspan="3" class="text-end">合計</td>

                            {{-- 期初 --}}
                            <td class="text-end">{{ number_format($sumOpeningDebit, 2) }}</td>
                            <td class="text-end">{{ number_format($sumOpeningCredit, 2) }}</td>

                            {{-- 本期 --}}
                            <td class="text-end">{{ number_format($sumPeriodDebit, 2) }}</td>
                            <td class="text-end">{{ number_format($sumPeriodCredit, 2) }}</td>

                            {{-- 期末 --}}
                            <td class="text-end">{{ number_format($sumEndingDebit, 2) }}</td>
                            <td class="text-end">{{ number_format($sumEndingCredit, 2) }}</td>
                        </tr>

                        @if($sumEndingDebit !== $sumEndingCredit)
                        <tr>
                            <td colspan="9" class="text-center text-danger fw-bold">
                                ⚠ 借貸不平衡，差額 {{ number_format(abs($sumEndingDebit - $sumEndingCredit), 2) }}
                            </td>
                        </tr>
                        @endif
                    </tfoot>
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
