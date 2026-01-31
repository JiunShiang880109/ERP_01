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
                    <li class="breadcrumb-item active" aria-current="page">明細分類帳查詢</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2">
                <form method="GET" action="{{route('accountReport.detailedLedger')}}">
                    
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
                            @forelse($rows ?? collect() as $r)

                                {{-- 科目段落標題 --}}
                                @if(($r['type'] ?? '') === 'subject_header')
                                    <tr class="table-primary fw-bold">
                                        <td></td>
                                        <td>{{ $r['subject_code'] ?? '-' }}</td>
                                        <td colspan="9">{{ $r['subject_name'] ?? '-' }}</td>
                                    </tr>

                                {{-- 期初 --}}
                                @elseif(($r['type'] ?? '') === 'opening')
                                    <tr class="table-light">
                                        <td></td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">{{ $r['note'] ?? '期初餘額' }}</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">-</td>
                                        <td class="text-end">{{ number_format((float)($r['balance'] ?? 0), 2) }}</td>
                                    </tr>

                                {{-- 本期分錄 --}}
                                @elseif(($r['type'] ?? '') === 'entry')
                                    <tr>
                                        <td>
                                            {{-- 你可以在這裡放「檢視傳票」等操作按鈕，若沒有就留空 --}}
                                        </td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td class="text-end">{{ $r['date'] ?? '-' }}</td>
                                        <td class="text-end">{{ $r['voucher_type'] ?? '-' }}</td>
                                        <td class="text-end">{{ $r['voucher_kind'] ?? '-' }}</td>
                                        <td class="text-end">{{ $r['voucher'] ?? '-' }}</td>
                                        <td class="text-end">{{ $r['note'] ?? '-' }}</td>
                                        <td class="text-end">
                                            {{ isset($r['debit']) && $r['debit'] !== null ? number_format((float)$r['debit'], 2) : '-' }}
                                        </td>
                                        <td class="text-end">
                                            {{ isset($r['credit']) && $r['credit'] !== null ? number_format((float)$r['credit'], 2) : '-' }}
                                        </td>
                                        <td class="text-end">{{ number_format((float)($r['balance'] ?? 0), 2) }}</td>
                                    </tr>

                                {{-- 每科目小計 --}}
                                @elseif(($r['type'] ?? '') === 'subtotal')
                                    <tr class="table-secondary fw-bold">
                                        <td></td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td class="text-end" colspan="5">{{ $r['note'] ?? '本期合計' }}</td>
                                        <td class="text-end">{{ number_format((float)($r['total_debit'] ?? 0), 2) }}</td>
                                        <td class="text-end">{{ number_format((float)($r['total_credit'] ?? 0), 2) }}</td>
                                        <td class="text-end">{{ number_format((float)($r['ending_balance'] ?? 0), 2) }}</td>
                                    </tr>
                                @endif

                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted">查無資料</td>
                                </tr>
                            @endforelse
                        </tbody>

                        <tfoot>
                            <tr class="table-dark fw-bold">
                                <td colspan="8" class="text-end">全部科目本期合計</td>
                                <td class="text-end">{{ number_format((float)($totalDebit ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float)($totalCredit ?? 0), 2) }}</td>
                                <td class="text-end">-</td>
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
