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
        <div class="breadcrumb-title pe-3">帳簿類</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{url()->previous()}}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">總分類帳查詢</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2">
                <form method="GET" action="{{route('accountReport.generalLedger')}}">
                    
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
                        <div class="col-auto fw-bold">科目</div>
                        <select name="subject_key_start" class="form-select subject-select">
                            <option value="">請選擇科目</option>

                            @foreach($subjects->groupBy(fn($s) =>
                                $s->main_code.'-'.$s->sub_code.'-'.$s->item_code
                            ) as $group)

                                @php $item = $group->first(); @endphp

                                <optgroup label="{{ $item->main_code }}{{ $item->sub_code }}{{ $item->item_code }} {{ $item->item_name }}">
                                    @if($group->whereNotNull('ledger_code')->count())
                                        {{-- 有明細科目 --}}
                                        @foreach($group as $row)
                                            <option value="{{ $row->main_code }}-{{ $row->sub_code }}-{{ $row->item_code }}-{{ $row->ledger_code }}"
                                                {{ request('subject_key_start') == "{$row->main_code}-{$row->sub_code}-{$row->item_code}-{$row->ledger_code}" ? 'selected' : '' }}>
                                                {{ $row->ledger_code }} {{ $row->ledger_name }}
                                            </option>
                                        @endforeach
                                    @else
                                        {{-- 沒有明細，選主科目 --}}
                                        <option value="{{ $item->main_code }}-{{ $item->sub_code }}-{{ $item->item_code }}-0"
                                            {{ request('subject_key_start') == "{$item->main_code}-{$item->sub_code}-{$item->item_code}-0" ? 'selected' : '' }}>
                                            {{ $item->item_name }}
                                        </option>
                                    @endif
                                </optgroup>
                            @endforeach
                        </select>
                        ~
                        <select name="subject_key_end" class="form-select subject-select">
                            <option value="">請選擇科目</option>

                            @foreach($subjects->groupBy(fn($s) =>
                                $s->main_code.'-'.$s->sub_code.'-'.$s->item_code
                            ) as $group)

                                @php $item = $group->first(); @endphp

                                <optgroup label="{{ $item->main_code }}{{ $item->sub_code }}{{ $item->item_code }} {{ $item->item_name }}">
                                    @if($group->whereNotNull('ledger_code')->count())
                                        {{-- 有明細科目 --}}
                                        @foreach($group as $row)
                                            <option value="{{ $row->main_code }}-{{ $row->sub_code }}-{{ $row->item_code }}-{{ $row->ledger_code }}"
                                                {{ request('subject_key_end') == "{$row->main_code}-{$row->sub_code}-{$row->item_code}-{$row->ledger_code}" ? 'selected' : '' }}>
                                                {{ $row->ledger_code }} {{ $row->ledger_name }}
                                            </option>
                                        @endforeach
                                    @else
                                        {{-- 沒有明細，選主科目 --}}
                                        <option value="{{ $item->main_code }}-{{ $item->sub_code }}-{{ $item->item_code }}-0"
                                            {{ request('subject_key_end') == "{$item->main_code}-{$item->sub_code}-{$item->item_code}-0" ? 'selected' : '' }}>
                                            {{ $item->item_name }}
                                        </option>
                                    @endif
                                </optgroup>
                            @endforeach
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
    @if($isSearched)
        <div class="col-lg-12">
            <div class="card alert">
                <div class="table-responsive invoice_list">
                    <table class="table table-hover" id="example">
                        <thead>
                            <tr>
                                <td>操作</td>
                                <td>科目編號</td>
                                <td>科目名稱</td>
                                <td class="text-end">傳票日期</td>
                                <td class="text-end">傳票類別</td>
                                <td class="text-end">傳票性質</td>
                                <td class="text-end">傳票編號</td>
                                <td class="text-end">摘要</td>
                                <td class="text-end">借方金額</td>
                                <td class="text-end">貸方金額</td>
                                <td class="text-end">餘額</td>
                            </tr>
                        </thead>
                        <tbody style="vertical-align:middle;">
                        @foreach($rows as $row)
                            <tr>
                                {{-- 操作 --}}
                                <td></td>

                                {{-- 科目編號 --}}
                                <td>{{ $row['subject_code'] ?? '-' }}</td>

                                {{-- 科目名稱 --}}
                                <td>{{ $row['subject_name'] ?? '-' }}</td>

                                {{-- 傳票日期 --}}
                                <td class="text-end">
                                    {{ $row['date'] ?? '' }}
                                </td>

                                {{-- 傳票類別 --}}
                                <td class="text-end">
                                    {{ $row['voucher_type'] ?? '' }}
                                </td>

                                {{-- 傳票性質 --}}
                                <td class="text-end">
                                    {{ $row['voucher_kind'] ?? '' }}
                                </td>

                                {{-- 傳票編號 --}}
                                <td class="text-end">
                                    {{ $row['voucher'] ?? '' }}
                                </td>

                                {{-- 摘要 --}}
                                <td>
                                    {{ $row['note'] }}
                                </td>

                                {{-- 借方 --}}
                                <td class="text-end">
                                    {{ $row['debit'] !== null ? number_format($row['debit'], 2) : '0' }}
                                </td>

                                {{-- 貸方 --}}
                                <td class="text-end">
                                    {{ $row['credit'] !== null ? number_format($row['credit'], 2) : '0' }}
                                </td>

                                {{-- 餘額 --}}
                                <td class="text-end">
                                    {{ number_format($row['balance'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td colspan="8" class="text-end">本期合計</td>
                                <td class="text-end">{{number_format($totalDebit, 2)}}</td>
                                <td class="text-end">{{number_format($totalCredit, 2)}}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
    @else
        <div class="alert alert-info">
            請設定查詢條件
        </div>
    @endif

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
            order: [[3, 'asc']], // 以傳票日期排序
            columnDefs: [
                { targets: [9,10], className: 'text-end' }
            ],
        });

    });
</script>
<script>
    //初始化科目選單
    function initSubjectSelect(context = document) {
        $(context).find('.subject-select').select2({
            placeholder: '請輸入科目編號或名稱',
            allowClear: true,
            width: '250px',   // 可調整
            matcher: function (params, data) {
                // 預設顯示全部
                if ($.trim(params.term) === '') {
                    return data;
                }

                if (typeof data.text === 'undefined') {
                    return null;
                }

                const term = params.term.toLowerCase();
                const text = data.text.toLowerCase();

                // 同時比對：顯示文字 + optgroup label
                if (text.includes(term) ||
                    (data.element && data.element.parentElement &&
                    data.element.parentElement.label &&
                    data.element.parentElement.label.toLowerCase().includes(term))
                ) {
                    return data;
                }

                return null;
            }
        });
    }
</script>
<script>
    $(document).ready(function(){
        initSubjectSelect();
    })
</script>

@endsection
